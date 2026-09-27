# TZ — Hokimliklar bo'yicha multi-tenant arxitektura

**Loyiha:** ҲРТ (Ҳужжат Рақамлаштириш Тизими) — Хоразм вилояти ҳокимлиги
**Hujjat versiyasi:** 1.0
**Sana:** 2026-04-08
**Maqom:** Tasdiqlash uchun

---

## 1. Maqsad va kontekst

### 1.1. Asosiy maqsad
Hozirgi tizim **bitta markazlashgan dashboard** (Хоразм вилояти ҳокимлиги) sifatida ishlamoqda. Yangi talab — har bir tuman/shahar hokimligi uchun **alohida dashboard** va boshqaruv panelini joriy etish.

### 1.2. Strategik maqsadlar
1. **Markazlashgan nazorat** — viloyat ҳokimligi barcha tumanlarni ko'radi va tahlil qiladi
2. **Lokal mustaqillik** — har bir tuman/shahar ҳokimligi o'z faoliyatini mustaqil yuritadi:
   - Foydalanuvchilarni boshqarish
   - O'z chora-tadbirlarini rejalashtirish
   - O'z kadrlarini yuritish
   - Hokim yordamchilari, yoshlar yetakchilari va boshqa lokal vazifalar
3. **Modulli kengayish** — kelajakdagi har bir modul (yoshlar, mahallalar, tadbirkorlik va h.k.) avtomatik ravishda tuman kesimida ishlaydi

### 1.3. Foydalanuvchilar tabaqalari

| Daraja | Kim | Ko'lam |
|---|---|---|
| **L1** | Super Admin (markaziy) | Barcha hokimliklar |
| **L2** | Viloyat hokimligi xodimlari | Barcha tumanlar (read) + viloyat (full) |
| **L3** | Tuman/shahar admin | Faqat o'z tumani |
| **L4** | Tuman xodimlari (rol asosida) | Faqat o'z tumani, modulga qarab cheklangan |

---

## 2. Hozirgi holat (As-Is) tahlili

### 2.1. Mavjud arxitektura

**Departments ierarxiyasi:**
```
viloyat (parent_id=null)
  └─ bo'limlar (parent_id=1)
shahar (parent_id=null)
  └─ bo'limlar (parent_id=N)
tuman (parent_id=null)
  └─ bo'limlar (parent_id=M)
```

**User → Department:** `users.department_id` orqali (user qaysi bo'lim/boshqarmaga tegishli)

### 2.2. Kamchiliklar (Gap analysis)

| # | Muammo | Ta'sir |
|---|---|---|
| 1 | `ControlPlan` da hokimlik ID yo'q (`created_by` faqat user) | Tumanlar bir-birining rejasini ko'ra oladi |
| 2 | `Employee` da hokimlik ID yo'q (`department_id` — bo'lim, lekin parent hokimlik aniqlanmagan) | Kadrlarni ajratish murakkab |
| 3 | Hech bir Eloquent model'da global tenant scope yo'q | Har query'da qo'lda filterlash kerak |
| 4 | Search Service tenant filter ishlatmaydi | Tumanlar ma'lumotlari bir joyda chiqadi |
| 5 | Dashboard barcha foydalanuvchilarga bir xil ko'rinadi | Tuman admini boshqa tumanni ko'radi |
| 6 | Rollar global (super-admin) — tenant darajasida emas | Tuman admini super-admin emas, lekin local-admin kerak |
| 7 | Auth shared (single login) — OK, lekin tenant context yo'q | Login holatida qaysi tumanga tegishliligini eslab qolish kerak |

---

## 3. Yangi arxitektura (To-Be)

### 3.1. Multi-tenancy strategiyasi

**Tanlov:** *Single Database + Tenant Column* (shared schema, partitioning by `hokimlik_id`)

**Sabab:**
- 1 viloyat + 14 tuman/shahar = 15 ta tenant — kichik miqdor
- Markaziy hisobotlar uchun JOIN bo'yicha tezroq
- Backup/migration osonroq
- Inson resurslari kichik (alohida DB shart emas)

**Muqobil yechimlar (rad etilgan):**
- ❌ Database per tenant — overkill, deploy murakkab
- ❌ Schema per tenant — MySQL'da yomon ishlaydi (PostgreSQL'da yaxshi)

### 3.2. Tenant kontekst

**Tenant** = top-level hokimlik (`departments` da `parent_id IS NULL` bo'lgan yozuv)

```
TENANT (viloyat hokimligi, ID=1)
  └─ Iqtisodiyot boshqarmasi (parent_id=1)
  └─ Qurilish boshqarmasi (parent_id=1)

TENANT (Urganch shahar hokimligi, ID=15)
  └─ Iqtisodiyot boshqarmasi (parent_id=15)
  └─ Qurilish boshqarmasi (parent_id=15)
```

**Foydalanuvchining tenant'ini aniqlash algoritmi:**
```
user.department.parent_id IS NULL ? user.department_id : user.department.parent_id
```

### 3.3. Yangi modellar va munosabatlar

```
┌─────────────────────────┐
│ Department (Hokimlik)   │ ← TENANT
│ - id, parent_id, type   │
│ - is_tenant (computed)  │
└─────────────┬───────────┘
              │ 1:N
   ┌──────────┴──────────┐
   ↓                     ↓
┌─────────┐         ┌──────────────┐
│ User    │         │ ControlPlan  │  ← har birida `hokimlik_id`
└─────────┘         └──────────────┘
   ↓
┌──────────┐
│ Employee │  ← `hokimlik_id`
└──────────┘
```

---

## 4. Database o'zgarishlari

### 4.1. Yangi migration: tenant column qo'shish

```sql
-- control_plans
ALTER TABLE control_plans
  ADD COLUMN hokimlik_id BIGINT UNSIGNED NULL AFTER created_by,
  ADD INDEX idx_hokimlik (hokimlik_id),
  ADD FOREIGN KEY (hokimlik_id) REFERENCES departments(id) ON DELETE RESTRICT;

-- employees
ALTER TABLE employees
  ADD COLUMN hokimlik_id BIGINT UNSIGNED NULL AFTER department_id,
  ADD INDEX idx_hokimlik (hokimlik_id),
  ADD FOREIGN KEY (hokimlik_id) REFERENCES departments(id) ON DELETE RESTRICT;
```

**Backfill:** mavjud yozuvlar uchun `hokimlik_id` ni `created_by` user'ning departmenti orqali to'ldirish.

### 4.2. Department modelda computed field

```php
class Department {
    public function tenant(): Department {
        return $this->parent_id === null ? $this : $this->parent;
    }

    public function getIsTenantAttribute(): bool {
        return $this->parent_id === null;
    }
}
```

### 4.3. User modelga helper

```php
class User {
    public function getHokimlikIdAttribute(): ?int {
        return $this->department?->parent_id ?? $this->department_id;
    }

    public function isFromTenant(int $hokimlikId): bool {
        return $this->hokimlik_id === $hokimlikId;
    }
}
```

---

## 5. Backend o'zgarishlari

### 5.1. Global Scope — `BelongsToTenant` trait

```php
trait BelongsToTenant {
    protected static function bootBelongsToTenant(): void {
        // Avtomatik filter: faqat foydalanuvchining tenant'iga tegishli yozuvlar
        static::addGlobalScope('tenant', function (Builder $builder) {
            $user = auth()->user();
            if (! $user || $user->hasRole('super-admin')) return;
            if ($user->hasRole('viloyat-admin')) return; // viloyat hammasini ko'radi

            $builder->where(static::TENANT_COLUMN, $user->hokimlik_id);
        });

        // Yaratilayotganda avtomatik tenant biriktirish
        static::creating(function ($model) {
            if (! $model->{static::TENANT_COLUMN} && auth()->check()) {
                $model->{static::TENANT_COLUMN} = auth()->user()->hokimlik_id;
            }
        });
    }
}
```

**Foydalanish:**
```php
class ControlPlan extends Model {
    use BelongsToTenant;
    public const TENANT_COLUMN = 'hokimlik_id';
}

class Employee extends Model {
    use BelongsToTenant;
    public const TENANT_COLUMN = 'hokimlik_id';
}
```

### 5.2. Yangi rollar va ruxsatlar

**Yangi rollar (3 ta):**
| Rol | Tenant scope | Vazifa |
|---|---|---|
| `viloyat-admin` | All tenants (read-only on others) | Viloyat hokim apparati |
| `tuman-admin` | Own tenant only | Tuman/shahar hokimligi admin |
| `tuman-mutaxassis` | Own tenant only | Tuman/shahar mutaxassisi |

**Yangi permissionlar:**
```
tenant.view-all              # Faqat super-admin va viloyat-admin
tenant.manage-users          # Tuman admin o'z foydalanuvchilarini yaratish
yoshlar.view, yoshlar.create, yoshlar.update, yoshlar.delete
hokim-yordamchilari.view, hokim-yordamchilari.create, ...
```

### 5.3. TenantMiddleware

```php
class EnsureTenantAccess {
    public function handle(Request $request, Closure $next) {
        $user = $request->user();
        if (! $user) abort(401);

        // Tenant context'ni request'ga biriktirish
        $request->attributes->set('hokimlik_id', $user->hokimlik_id);
        $request->attributes->set('hokimlik', $user->department?->tenant());

        return $next($request);
    }
}
```

### 5.4. Policy yangilash

Har bir Policy methodda tenant tekshiruvi:

```php
class ControlPlanPolicy {
    public function view(User $user, ControlPlan $plan): bool {
        if ($user->hasRole('super-admin')) return true;
        if ($user->hasRole('viloyat-admin')) return true;
        return $plan->hokimlik_id === $user->hokimlik_id;
    }
}
```

### 5.5. Search service'lar tenant'ga moslashtirilsin

`EmployeeSearchService` — global scope avtomatik ishlaydi. **Action talab qilinmaydi** (trait orqali).

---

## 6. Frontend o'zgarishlari

### 6.1. Tenant context (Inertia shared data)

`HandleInertiaRequests` da:
```php
'tenant' => fn () => auth()->user() ? [
    'id' => auth()->user()->hokimlik_id,
    'name' => auth()->user()->department?->tenant()?->name_cyr,
    'type' => auth()->user()->department?->tenant()?->type, // viloyat/shahar/tuman
] : null,
```

### 6.2. Sidebar yangilash

- Header'da tenant nomi: "Хоразм вилояти ҳокимлиги" yoki "Урганч шаҳар ҳокимлиги"
- Super-admin uchun tenant switcher dropdown (qaysi tumanni ko'rishni tanlash)

### 6.3. Dashboard

**3 xil dashboard view:**
1. **Super-admin / Viloyat-admin Dashboard** — har bir tuman bo'yicha statistika kartochkasi (drill-down)
2. **Tuman-admin Dashboard** — faqat shu tuman ma'lumotlari
3. **Mutaxassis Dashboard** — minimal: o'ziga biriktirilgan vazifalar

### 6.4. URL strategiyasi

**Variant A (tanlangan):** Implicit tenant — URL'da hokimlik ID yo'q, server avtomatik aniqlaydi.
- `/control-plans` → joriy foydalanuvchi tenanti uchun
- Super-admin uchun query param: `/control-plans?tenant=15`

**Variant B (rad etilgan):** Path-based — `/tenants/15/control-plans` — overhead ko'p, har route prefiks qo'shish kerak.

---

## 7. Yangi modullar (kelajakdagi)

### 7.1. Hokim yordamchilari moduli (`hokim-yordamchilari`)

**Maqsad:** Har bir tuman hokimi yordamchilarini va ularning vazifalarini boshqarish.

**Database:**
```
hokim_yordamchilari:
  id, hokimlik_id (tenant), user_id (FK→users)
  yo'nalish (enum: iqtisodiyot, qurilish, qishloq, ijtimoiy, madaniyat)
  mahalla_id (nullable — agar mahalla yetakchisi bo'lsa)
  start_date, end_date
  is_active, notes
  timestamps
```

**Vazifalar (assignments):**
```
hy_assignments:
  id, hokim_yordamchisi_id, title, description
  due_date, status (planned/in_progress/done/cancelled)
  documents (JSON yoki separate table)
  timestamps
```

### 7.2. Yoshlar yetakchilari moduli (`yoshlar-yetakchilari`)

**Maqsad:** Mahalla darajasidagi yoshlar yetakchilari va ularning faoliyati.

**Database:**
```
yoshlar_yetakchilari:
  id, hokimlik_id (tenant), user_id (FK→users)
  mahalla_id (FK→mahallas)
  start_date, end_date, is_active
  timestamps

yy_events:
  id, yoshlar_yetakchisi_id
  event_type (sport, ta'lim, ma'naviyat, ish)
  participants_count, date, description
  documents
  timestamps
```

### 7.3. Modul shabloni (template)

Har yangi modul shu pattern'ga bo'ysunadi:
1. **Migration** — `hokimlik_id` ustun majburiy
2. **Model** — `BelongsToTenant` trait
3. **Policy** — tenant tekshiruvi
4. **Permissions** — `MODUL_NAME.view`, `MODUL_NAME.create` va h.k.
5. **Frontend** — joriy tenant kontekstida
6. **Dashboard widget** — agregatsiya `hokimlik_id` bo'yicha

---

## 8. Migratsiya rejasi (Step-by-step)

### Phase 1: Database (1 hafta)
- [ ] `hokimlik_id` ustunlarini ControlPlan, Employee va boshqa tegishli jadvallarga qo'shish
- [ ] Mavjud ma'lumotlarni backfill qilish (default: viloyat hokimligi ID=1)
- [ ] Foreign key + index'lar
- [ ] Migration testi (rollback ham ishlashi)

### Phase 2: Backend foundation (1 hafta)
- [ ] `BelongsToTenant` trait
- [ ] User model'da `hokimlik_id` accessor
- [ ] Department model'da `tenant()` method
- [ ] `EnsureTenantAccess` middleware
- [ ] `HandleInertiaRequests` da `tenant` shared
- [ ] Yangi rollar: `viloyat-admin`, `tuman-admin`, `tuman-mutaxassis`

### Phase 3: Existing modullarni moslashtirish (1 hafta)
- [ ] ControlPlan: scope, policy, controller filter
- [ ] Employee: scope, policy, search service
- [ ] User: tuman-admin o'z tenanti foydalanuvchilarini boshqarish
- [ ] Audit log: tenant ID ham log'da

### Phase 4: Frontend (1 hafta)
- [ ] Sidebar — tenant nomi va tenant switcher (super-admin)
- [ ] Dashboard — 3 ko'rinish (super, viloyat, tuman)
- [ ] Index sahifalarda tenant filter (super-admin uchun)
- [ ] URL parametrlar bilan tenant tanlash

### Phase 5: Yangi modul — Hokim yordamchilari (1.5 hafta)
- [ ] Migration, Model, Controller, CRUD
- [ ] Vazifa biriktirish, hujjat yuklash
- [ ] Dashboard widget
- [ ] Tuman admin o'z hokim yordamchilarini boshqaradi

### Phase 6: Yangi modul — Yoshlar yetakchilari (1.5 hafta)
- [ ] Migration, Model, Controller, CRUD
- [ ] Mahalla bilan bog'lanish
- [ ] Tadbirlar (events) qo'shish
- [ ] Dashboard widget

### Phase 7: Test va deploy (1 hafta)
- [ ] Har rol uchun e2e testlar
- [ ] Tenant izolatsiya security testlari (cross-tenant access bloklanishi)
- [ ] Performance test (N+1 query yo'qligi)
- [ ] Production deploy + monitoring

**Jami muddat:** 8 hafta (taxminan 2 oy)

---

## 9. Xavfsizlik talablari

### 9.1. Tenant izolyatsiyasi
- Hech bir API endpoint cross-tenant ma'lumot qaytarmasligi kerak
- Global scope **MAJBURIY** — har query avtomatik filterlanishi
- Mass assignment'da `hokimlik_id` qo'lda berib bo'lmaydi (creating event'da auto-fill)

### 9.2. Audit majburiyatlari
Har CRUD action'da log:
- Kim qildi (`causer_id`)
- Qaysi tenant ichida (`hokimlik_id`)
- Eski va yangi qiymatlar
- IP va vaqt

### 9.3. Privilege escalation oldini olish
- Tuman admin **boshqa tuman** foydalanuvchisini yarata olmaydi (validatsiya)
- Tuman admin **super-admin** roli bera olmaydi (whitelist)
- Role assignment'da policy tekshiruvi

### 9.4. URL manipulation
Super-admin'dan tashqari hech kim `?tenant=N` orqali boshqa tumanni ko'ra olmaydi (middleware tekshiruvi).

---

## 10. Testing va sifat ko'rsatkichlari

### 10.1. Test coverage talablari
- Unit testlar: 80%+
- Feature testlar: barcha policy + tenant izolyatsiya
- Penetration test: cross-tenant access urinishlari

### 10.2. Performance ko'rsatkichlari
| Operatsiya | Maqsad |
|---|---|
| Dashboard load | < 500ms |
| Index sahifa (25 yozuv) | < 300ms |
| Tenant switching (super-admin) | < 200ms |
| ControlPlan show (10 band) | < 700ms |

### 10.3. UX ko'rsatkichlari
- Tuman admin tizimga kirgach — DARHOL o'z dashboardni ko'radi (autodetect)
- Super-admin uchun tenant switcher ko'rinarli, lekin halaqit qilmaydi
- Tenant chegarasi har joyda aniq ko'rsatiladi (sidebar header)

---

## 11. Risk va kelishilgan muammolar

| Risk | Ehtimollik | Yumshatish |
|---|---|---|
| Mavjud ma'lumotlarni backfill xatosi | O'rta | Backup + rollback skript |
| Global scope all queries'ga ta'sir qiladi | Yuqori | Test coverage 100%, oldindan staging'da sinash |
| Tuman admin ortiqcha huquqlar oladi | O'rta | Permission whitelist, tenant validatsiyasi |
| Performance — `hokimlik_id` JOIN'lar | Past | Index, eager loading |
| Foydalanuvchilar bir tumandan boshqasiga o'tsa | Past | `department_id` o'zgarishi → audit log + role qayta tayinlash |

---

## 12. Hujjatlashtirish va o'qitish

### 12.1. Super-admin uchun
- Multi-tenant rejimi qanday ishlashi
- Yangi tuman qo'shish jarayoni
- Cross-tenant hisobotlar
- Audit log filterlari

### 12.2. Tuman admin uchun
- O'z foydalanuvchilarini yaratish
- Hokim yordamchilarini boshqarish
- Yoshlar yetakchilarini boshqarish
- O'z chora-tadbir rejasini yaratish

### 12.3. Mutaxassis uchun
- O'ziga biriktirilgan vazifalar
- Hisobot yozish
- Hujjat yuklash

---

## 13. Qabul mezonlari (Definition of Done)

Loyiha **tugagan** deb hisoblanadi, agar:

- [ ] 14+ tuman/shahar uchun alohida dashboardlar ishlaydi
- [ ] Tuman admin **faqat o'z tumani** ma'lumotlarini ko'radi (penetration test o'tildi)
- [ ] Super-admin barcha tumanlarni ko'radi va switching mumkin
- [ ] Hokim yordamchilari moduli to'liq ishlaydi (CRUD + dashboard)
- [ ] Yoshlar yetakchilari moduli to'liq ishlaydi (CRUD + dashboard)
- [ ] Mavjud modullar (Kadrlar, Nazorat reja) tenant'ga moslashtirilgan
- [ ] Audit log har action'da `hokimlik_id` saqlaydi
- [ ] Performance ko'rsatkichlari maqsadlarga mos
- [ ] Hujjatlashtirilgan: API docs + foydalanuvchi qo'llanmasi
- [ ] Production'ga deploy qilingan va 1 hafta stabil ishlagan

---

## 14. Ma'qullash

| Rol | Ism | Imzo | Sana |
|---|---|---|---|
| Loyiha rahbari | _________ | | |
| Bosh dasturchi | _________ | | |
| Hokim apparati vakili | _________ | | |
| Axborot xavfsizligi | _________ | | |

---

## Ilova A: Yangi rollar matritsasi

| Permission | super-admin | viloyat-admin | tuman-admin | hokim-maslahatchisi | mutaxassis | kadrlar-xodimi |
|---|---|---|---|---|---|---|
| `tenant.view-all` | ✅ | ✅ | ❌ | ❌ | ❌ | ❌ |
| `tenant.manage-users` | ✅ | ❌ | ✅ (own) | ❌ | ❌ | ❌ |
| `kadrlar.view` | ✅ | ✅ (own/all) | ✅ (own) | ✅ (own) | ❌ | ✅ (own) |
| `kadrlar.create` | ✅ | ❌ | ✅ (own) | ❌ | ❌ | ✅ (own) |
| `tadbirlar.view` | ✅ | ✅ (all) | ✅ (own) | ✅ (own) | ✅ (own) | ❌ |
| `tadbirlar.create` | ✅ | ❌ | ✅ (own) | ❌ | ✅ (own) | ❌ |
| `tadbirlar.update` | ✅ | ❌ | ✅ (own) | ❌ | ✅ (own) | ❌ |
| `yoshlar.view` | ✅ | ✅ | ✅ (own) | ❌ | ❌ | ❌ |
| `yoshlar.manage` | ✅ | ❌ | ✅ (own) | ❌ | ❌ | ❌ |
| `hokim-yordamchilari.view` | ✅ | ✅ | ✅ (own) | ✅ (own) | ❌ | ❌ |
| `hokim-yordamchilari.manage` | ✅ | ❌ | ✅ (own) | ❌ | ❌ | ❌ |
| `audit.view` | ✅ | ✅ (all) | ✅ (own) | ✅ (own) | ❌ | ❌ |
| `dashboard.cross-tenant` | ✅ | ✅ | ❌ | ❌ | ❌ | ❌ |

> `(own)` = faqat o'z tenanti, `(all)` = barcha tenantlar

---

## Ilova B: Texnik yo'l xaritasi (Gantt)

```
Hafta 1: ████ Database
Hafta 2: ████ Backend Foundation
Hafta 3: ████ Migrate existing modules
Hafta 4: ████ Frontend
Hafta 5-6: ████████ Hokim yordamchilari
Hafta 7-8: ████████ Yoshlar yetakchilari
Hafta 9: ████ Test va Deploy
```

---

## Ilova C: API o'zgarishlari xulosasi

**Yangi endpoints:**
- `GET /api/tenants` — Super-admin uchun barcha hokimliklar
- `GET /api/tenants/{id}/dashboard` — Tenant dashboard data
- `POST /api/tenants/switch` — Super-admin uchun tenant context o'zgarishi
- `/hokim-yordamchilari/*` — Resource (CRUD)
- `/yoshlar-yetakchilari/*` — Resource (CRUD)
- `/yoshlar-yetakchilari/{id}/events` — Tadbirlar

**O'zgartirilgan endpoints:** Barcha mavjud endpointlar tenant scope orqali avtomatik filterlash.

---

**TZ versiyasi:** 1.0
**Loyihaning bo'limi:** Multi-tenant arxitektura
**Mas'ul:** Bosh dasturchi
