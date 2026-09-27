# TZ — Yoshlar Murojaatlari va AI Yordamida Hal Qilish Tizimi

**Loyiha:** ҲРТ — Yoshlar uchrashuvlari va murojaatlarni boshqarish moduli
**Hujjat versiyasi:** 1.0
**Sana:** 2026-04-09
**Maqom:** Strategik TZ, ma'qullashga taqdim

---

## 1. Maqsad va biznes-konteksti

### 1.1 Muammo (As-Is)

Hokim yoshlar bilan uchrashuvlarda **takrorlanuvchi muammolar** ko'tariladi:
- Kredit (mikro, ipoteka, biznes start-up)
- Yer (tomorqa, ishlab chiqarish, dehqonchilik)
- Ish o'rni va kasbga o'qitish
- Ta'lim (universitet, granty)
- Ijtimoiy yordam

Hozir bu murojaatlar:
- ❌ **Qog'ozda yoki Excel'da** yig'iladi
- ❌ Bir mahallada hal qilingan masalaning **boshqa mahallaga uzatilmaydi**
- ❌ **Statistik tahlil yo'q** — qaysi muammo qaysi tumanda eng ko'p
- ❌ Bir xil murojaatlar **takror-takror** keladi
- ❌ Mahalla yettiligi yagona reja bo'yicha emas, **ad-hoc** ishlaydi

### 1.2 To-Be Vision

**Yoshlar uchrashuvlari raqamli platforma** orqali olib boriladi. Har bir murojaat tizimga kiritiladi va:

1. **AI avtomatik tahlil** — kategoriya, ustuvorlik, hudud aniqlanadi
2. **O'xshash o'tgan ishlar** taqdim etiladi (vector search)
3. **Mahalla yettiligi**ga avtomatik yo'naltirish
4. **SLA monitoring** — javob muddatini kuzatish
5. **Statistik dashboardlar** — heat-map, trend, takrorlanish tahlili
6. **AI-yordamli javob loyihalari** — operatorga draft taklif

### 1.3 Maqsadli ko'rsatkichlar (KPI)

| KPI | Maqsad |
|---|---|
| Birinchi javob muddati | < 24 soat |
| To'liq hal qilish (kredit/yer) | < 30 kun |
| Mahalla darajasida hal qilish ulushi | > 70% |
| Takror murojaat (90 kun ichida) | < 10% |
| AI kategorizatsiya aniqligi | > 85% |
| Fuqaro qoniqish indeksi | > 4.0 / 5 |

---

## 2. Asosiy mantiq (Business Logic)

### 2.1 To'liq jarayon (lifecycle)

```
┌────────────────────┐
│ 1. UCHRASHUV      │  Hokim/o'rinbosar yoshlar bilan uchrashadi
│    (Meeting)       │  Joy: tuman/mahalla
└─────────┬──────────┘
          │
          ▼
┌────────────────────┐
│ 2. MUROJAAT KIRITISH│  Operator yoki shaxs o'zi
│    (Appeal intake) │  Kim: yoshlar yetakchisi, kotibyat
│                    │  Vositalar: web-form, mobil, voice-to-text
└─────────┬──────────┘
          │
          ▼
┌────────────────────┐
│ 3. AI TRIAGE       │  Avtomatik:
│                    │  - Kategoriya (kredit/yer/ish/...)
│                    │  - Sub-kategoriya
│                    │  - Ustuvorlik (oddiy/muhim/shoshilinch)
│                    │  - Hudud (mahalla, tuman)
│                    │  - O'xshash o'tgan ishlar
│                    │  - Ishonch darajasi (>0.85 auto-route)
└─────────┬──────────┘
          │
          ▼
┌────────────────────┐
│ 4. YO'NALTIRISH    │  Routing rules:
│    (Routing)       │  - Mahalla yettiligi (default)
│                    │  - Tuman bo'limi (mahalla yetolmasa)
│                    │  - Viloyat bo'limi (eskalatsiya)
│                    │  - Bir nechta yo'nalish (multi-assign)
└─────────┬──────────┘
          │
          ▼
┌────────────────────┐
│ 5. HAL QILISH      │  Mahalla yettiligi:
│    (Resolution)    │  - Yig'ilish o'tkazadi
│                    │  - Qaror chiqaradi (ha/yo'q + sabab)
│                    │  - Hujjat ilova qiladi
│                    │  - Status yangilaydi
│                    │  AI: javob loyihasi (draft) tayyorlaydi
└─────────┬──────────┘
          │
          ▼
┌────────────────────┐
│ 6. NAZORAT         │  SLA monitor:
│    (SLA monitor)   │  - Muddat o'tib ketsa eskalatsiya
│                    │  - Hokim panelda ko'rinadi
└─────────┬──────────┘
          │
          ▼
┌────────────────────┐
│ 7. FEEDBACK        │  Fuqaro:
│    (Citizen reply) │  - Qoniqishni baholaydi (1-5)
│                    │  - Izoh yozadi
│                    │  AI: sentiment tahlil, takror so'rov detection
└────────────────────┘
```

### 2.2 Mahalla yettiligi tarkibi

Tipik tarkib (har biriga rol biriktirilgan):
1. **Mahalla raisi** (yoki oqsoqol) — boshchilik
2. **Imom-xatib** — diniy va ijtimoiy maslahat
3. **Yoshlar yetakchisi** — yoshlar masalalari
4. **Ayollar yetakchisi (ATEM)** — oilaviy va ayollar masalalari
5. **Posbon (PPN inspektor)** — qonun-tartibot
6. **Maktab vakili** — ta'lim va tarbiya
7. **Soliq inspektori / Iqtisodiyot vakili** — kredit/biznes (talab bo'lsa)

> Tarkib har bir mahallada konfiguratsiya qilinadi, lekin kamida 5 a'zo bo'lishi shart.

### 2.3 Murojaat kategoriyalari

**Yuqori darajadagi 6 ta domen:**

| Domen | Sub-kategoriyalar | Default kim hal qiladi |
|---|---|---|
| 💰 **Kredit** | Mikro, Ipoteka, Biznes-start-up, Sotsial kredit | Mahalla → Bank vakili |
| 🌾 **Yer** | Tomorqa, Dehqonchilik, Ishlab-chiqarish, Tijorat | Mahalla → Hokimlik (yer bo'limi) |
| 💼 **Ish o'rni** | Kasbga o'qitish, Ish topish, Korxona ochish | Yoshlar yetakchisi → Bandlik markazi |
| 🎓 **Ta'lim** | Universitet, Granty, Magistratura, Xorij | Maktab vakili → Ta'lim bo'limi |
| 🏥 **Ijtimoiy** | Tibbiy yordam, Nogironlik, Ona-bola | ATEM → Ijtimoiy ta'minot |
| ⚖️ **Huquqiy** | Hujjat, Nikoh, Mehnat hujjati | Imom + Posbon → Hokimlik |

Har sub-kategoriya uchun **routing rules JSON** konfiguratsiya qilinadi.

---

## 3. AI komponent — qanday ishlaydi

### 3.1 AI vazifalar ro'yxati

| # | Vazifa | Texnologiya | Real-time? |
|---|---|---|---|
| 1 | Kategoriya aniqlash | LLM (zero-shot) + embeddings | Ha |
| 2 | Ustuvorlik aniqlash | Rules + LLM | Ha |
| 3 | O'xshash o'tgan ishlar topish | Vector similarity (HNSW) | Ha |
| 4 | Duplikat aniqlash (anti-spam) | Embedding similarity > 0.92 | Ha |
| 5 | Routing tavsiyasi | Rules + ML model | Ha |
| 6 | Javob loyihasi (draft) | LLM (RAG) | Asinxron |
| 7 | Trend va anomaly detection | Time-series + clustering | Kunlik batch |
| 8 | Hokim uchun resume | LLM summarization | Asinxron |
| 9 | Sentiment analysis (feedback) | LLM | Asinxron |
| 10 | Mahalla heat-map | Geo-spatial agregatsiya | Soat'lik batch |

### 3.2 AI arxitekturasi

```
┌──────────────────────────────────────────────────────────┐
│                    INTAKE QATLAMI                          │
│  Web form / Mobil / Voice-to-text (Whisper API)           │
└──────────────────────────────────────────────────────────┘
                          │
                          ▼
┌──────────────────────────────────────────────────────────┐
│                  PREPROCESSING                            │
│  - Lotin↔Kirill transliteratsiya                         │
│  - Tillarni aralashma normalizatsiya (Uz/Ru)              │
│  - Manzil parsing (mahalla/tuman aniqlash)                │
└──────────────────────────────────────────────────────────┘
                          │
                          ▼
┌──────────────────────────────────────────────────────────┐
│                  EMBEDDINGS                                │
│  Model: multilingual-e5-large yoki BGE-M3                 │
│  Saqlash: pgvector yoki Qdrant                            │
│  Index: HNSW (m=16, ef_construction=200)                  │
└──────────────────────────────────────────────────────────┘
                          │
            ┌─────────────┼─────────────┐
            ▼             ▼             ▼
   ┌──────────────┐  ┌─────────┐  ┌──────────────┐
   │ CLASSIFICATION│ │ SIMILAR │  │ DUPLICATE    │
   │ (Two-tier)   │  │ SEARCH  │  │ DETECTION    │
   │              │  │         │  │              │
   │ 1. Zero-shot │  │ k=10    │  │ Threshold    │
   │ 2. Fine-grain│  │ HNSW    │  │ 0.92+        │
   └──────┬───────┘  └────┬────┘  └──────┬───────┘
          │               │               │
          └───────────────┼───────────────┘
                          ▼
┌──────────────────────────────────────────────────────────┐
│                ROUTING ENGINE                              │
│  Rules (JSON) + ML score                                  │
│  Output: assigned_to, confidence                          │
│  Confidence < 0.75 → manual review queue                  │
└──────────────────────────────────────────────────────────┘
                          │
                          ▼
┌──────────────────────────────────────────────────────────┐
│              CASE MANAGEMENT                               │
│  Status, SLA, comments, documents                         │
└──────────────────────────────────────────────────────────┘
                          │
                          ▼
┌──────────────────────────────────────────────────────────┐
│            DRAFT GENERATION (RAG)                         │
│  LLM (GPT-4 / Claude) + similar cases context            │
│  Output: javob loyihasi (operator tahrirlaydi)            │
└──────────────────────────────────────────────────────────┘
                          │
                          ▼
┌──────────────────────────────────────────────────────────┐
│            ANALYTICS LAYER                                 │
│  - Heatmap (mahalla × kategoriya)                         │
│  - Trend (vaqt × kategoriya)                              │
│  - Anomaly detection (kunlik birdan o'sish)              │
│  - Cluster — yangi paydo bo'lgan muammo turi              │
└──────────────────────────────────────────────────────────┘
```

### 3.3 AI texnologiyalar tanlash

**Til modeli (LLM):**
- **Asosiy:** GPT-4 yoki Claude (rasmiy javob loyihalari uchun)
- **Yordamchi:** Local Llama 3.1 8B (PII tushunchasi va ichki klassifikatsiya)
- **Embedding:** `intfloat/multilingual-e5-large` (768-dim) yoki `BAAI/bge-m3` (1024-dim)

**Vector DB:**
- **Tanlangan:** PostgreSQL + pgvector (mavjud Laravel infra'ga qo'shimcha shart emas)
- **Alternativ:** Qdrant (agar 1M+ vector bo'lsa)

**Speech-to-text** (voice intake uchun):
- OpenAI Whisper API (yoki self-hosted Whisper Large-v3)

**OCR** (qog'oz arizalarni skanerdan olish uchun):
- Tesseract (uzb_cyrl) + GPT-4 vision yangiliklar uchun

### 3.4 Hybrid search (BM25 + vector)

```
1. Foydalanuvchi murojaati matnini embedding'ga o'tkazadi
2. Filter: kategoriya = "kredit", tuman = "Urganch"
3. Top-50: BM25 score (matn bo'yicha)
4. Top-50: Vector cosine similarity
5. Reciprocal Rank Fusion (RRF) bilan birlashtiriladi
6. Top-10 LLM reranker'ga beriladi (kontekst bilan)
7. Final result: 5 ta eng o'xshash holat
```

**Sabab:** Vector alone — manzil/ism/raqam yoqolib ketadi. BM25 — semantik tushunmaydi. Hybrid eng yuqori aniqlikni beradi.

---

## 4. Database arxitekturasi

### 4.1 Yangi jadvallar (15 ta)

```
┌─────────────────────────┐
│ youth_meetings          │  Uchrashuvlar
│ - id, hokimlik_id       │
│ - meeting_date          │
│ - location (mahalla_id) │
│ - participants_count    │
│ - chairman_id (User)    │
└─────────────────────────┘

┌─────────────────────────┐
│ citizen_appeals         │  Murojaatlar
│ - id, uuid              │
│ - hokimlik_id (tenant)  │
│ - meeting_id (nullable) │
│ - mahalla_id            │
│ - applicant_name        │
│ - applicant_phone       │
│ - applicant_jshshir     │
│ - applicant_birth_date  │
│ - body (TEXT)           │
│ - voice_path (nullable) │
│ - photo_path            │
│ - category_id (FK)      │
│ - sub_category_id (FK)  │
│ - priority (enum)       │
│ - status (enum)         │
│ - source (web/voice/    │
│           paper)        │
│ - confidence_score      │
│ - duplicate_of_id (FK)  │
│ - created_at            │
└─────────────────────────┘

┌─────────────────────────┐
│ appeal_categories       │  Kategoriyalar
│ - id, parent_id         │
│ - code (kredit/yer/...) │
│ - name_cyr, name_lat    │
│ - default_route_to      │
│ - sla_hours             │
└─────────────────────────┘

┌─────────────────────────┐
│ appeal_assignments      │  Yo'naltirishlar
│ - id, appeal_id         │
│ - assignee_type         │
│   (council/dept/user)   │
│ - assignee_id           │
│ - assigned_by           │
│ - reason                │
│ - status                │
│ - assigned_at           │
└─────────────────────────┘

┌─────────────────────────┐
│ mahalla_councils        │  Mahalla yettiligi
│ - id, mahalla_id        │
│ - name (default: Yett.) │
│ - is_active             │
└─────────────────────────┘

┌─────────────────────────┐
│ council_members         │  A'zolar
│ - id, council_id        │
│ - user_id (nullable)    │
│ - full_name             │
│ - role (rais/imom/...)  │
│ - phone                 │
│ - is_active             │
└─────────────────────────┘

┌─────────────────────────┐
│ council_decisions       │  Qarorlar
│ - id, appeal_id         │
│ - council_id            │
│ - meeting_date          │
│ - decision_type         │
│   (approve/reject/      │
│    escalate/info)       │
│ - decision_text         │
│ - voting_result (JSON)  │
│ - decided_at            │
└─────────────────────────┘

┌─────────────────────────┐
│ appeal_documents        │  Hujjatlar
│ - id, appeal_id         │
│ - file_path, type       │
│ - uploaded_by           │
└─────────────────────────┘

┌─────────────────────────┐
│ appeal_comments         │  Izohlar/yozishmalar
│ - id, appeal_id         │
│ - author_id, body       │
│ - is_internal           │
│ - created_at            │
└─────────────────────────┘

┌─────────────────────────┐
│ appeal_status_history   │  Status o'zgarishlari
│ - id, appeal_id         │
│ - from_status, to_      │
│ - changed_by, reason    │
└─────────────────────────┘

┌─────────────────────────┐
│ appeal_embeddings       │  AI embeddings
│ - id, appeal_id         │
│ - model_name            │
│ - vector (pgvector)     │
│ - created_at            │
│ INDEX: HNSW(vector)     │
└─────────────────────────┘

┌─────────────────────────┐
│ ai_classifications      │  AI tahlil natijalari
│ - id, appeal_id         │
│ - model_name            │
│ - category_predicted    │
│ - sub_category_predicted│
│ - priority_predicted    │
│ - confidence            │
│ - similar_appeals (JSON)│
│ - reasoning (TEXT)      │
│ - created_at            │
└─────────────────────────┘

┌─────────────────────────┐
│ ai_drafts               │  AI javob loyihalari
│ - id, appeal_id         │
│ - draft_text            │
│ - model_name            │
│ - based_on_cases (JSON) │
│ - approved_by_user      │
│ - approved_at           │
└─────────────────────────┘

┌─────────────────────────┐
│ citizen_feedback        │  Fuqaro feedback
│ - id, appeal_id         │
│ - rating (1-5)          │
│ - body                  │
│ - sentiment_score       │
│ - submitted_at          │
└─────────────────────────┘

┌─────────────────────────┐
│ routing_rules           │  Routing konfiguratsiya
│ - id, hokimlik_id       │
│ - category_id           │
│ - condition (JSON)      │
│ - assignee_type         │
│ - assignee_id           │
│ - sla_hours_override    │
└─────────────────────────┘
```

### 4.2 Tenant izolyatsiya

Barcha jadvallar **`hokimlik_id`** ustuni orqali izolyatsiya qilinadi:
- `BelongsToTenant` trait avtomatik scope
- Mahalla — `mahalla_id` orqali yana sub-filter
- Super-admin va viloyat-admin global ko'radi

### 4.3 PostgreSQL pgvector

```sql
CREATE EXTENSION IF NOT EXISTS vector;

CREATE TABLE appeal_embeddings (
    id BIGSERIAL PRIMARY KEY,
    appeal_id BIGINT NOT NULL REFERENCES citizen_appeals(id) ON DELETE CASCADE,
    model_name VARCHAR(100) NOT NULL,
    vector vector(1024) NOT NULL,
    created_at TIMESTAMP DEFAULT NOW()
);

CREATE INDEX appeal_embeddings_hnsw_idx
    ON appeal_embeddings USING hnsw (vector vector_cosine_ops)
    WITH (m = 16, ef_construction = 200);
```

> **Eslatma:** Hozirgi tizim MySQL'da. Bu modul uchun PostgreSQL'ga ko'chirish yoki MySQL + alohida vector DB (Qdrant) ishlatish.

---

## 5. Modullar va vazifalar

### 5.1 Modul ro'yxati

| # | Modul | Yangi/Mavjud | Murakkablik |
|---|---|---|---|
| 1 | Yoshlar uchrashuvlari | Yangi | O'rta |
| 2 | Murojaatlar (Appeals) | Yangi | Yuqori |
| 3 | Mahalla yettiligi | Yangi | O'rta |
| 4 | AI integration | Yangi | Yuqori |
| 5 | Routing engine | Yangi | O'rta |
| 6 | SLA monitoring | Yangi | O'rta |
| 7 | Statistika va dashboardlar | Yangi | Yuqori |
| 8 | Mobil ilova / Telegram bot | Yangi | O'rta |
| 9 | Hujjat boshqaruvi | Mavjud (kengaytma) | Quyi |
| 10 | Audit log | Mavjud | — |

### 5.2 Modul tafsilotlari

#### **5.2.1 Yoshlar uchrashuvlari**

- Hokim, o'rinbosar, hokim yordamchisi yoki yoshlar yetakchisi yaratadi
- Joy, sana, qatnashchilar
- Qatnashchilar ro'yxati (FIO + telefon)
- Uchrashuv davomida murojaatlar zudlik bilan yoziladi (tablet/telefon orqali)
- Uchrashuv yakunida AI **avtomatik resume** taqdim etadi:
  - Nechta murojaat bo'ldi
  - Qaysi kategoriyalar
  - Qaysi muammolar takrorlanmoqda
  - Eng dolzarb 3 ta savol

#### **5.2.2 Murojaatlar**

**Kirish kanallari:**
- Web-form (uchrashuvda yoki keyin)
- Telegram bot (oddiy fuqaro)
- Voice (audio yuklash → Whisper transkriptsiya)
- Qog'oz ariza skanerlash (OCR + AI parse)

**Lifecycle statuslar:**
```
draft → submitted → triaged → routed → in_review → 
decided → completed → closed
                          ↓
                    reopened (agar feedback yomon bo'lsa)
```

**Har holat uchun avtomatik amal:**
- `submitted` → AI triage queue'ga
- `triaged` → routing rules'ga
- `routed` → notification: yettilik, SMS fuqaroga
- `in_review` → SLA timer start
- `decided` → AI javob loyihasi tayyorlanadi
- `completed` → fuqaroga feedback so'rov

#### **5.2.3 Mahalla yettiligi**

- Har mahalla uchun bitta yettilik
- Konfiguratsiya: a'zolar va rollari
- Yig'ilishlar (oddiy/shoshilinch)
- Murojaatlarni ko'rib chiqish va qaror qabul qilish
- Ovoz berish (oddiy yoki yashirin)
- Qarorlarni hujjat sifatida saqlash (DOCX export)

#### **5.2.4 Routing engine**

**Rules format (JSON):**
```json
{
  "category": "kredit",
  "sub_category": "mikro_kredit",
  "conditions": [
    {"field": "amount", "op": "<", "value": 50000000},
    {"field": "applicant_age", "op": "between", "value": [18, 30]}
  ],
  "route_to": {
    "type": "council",
    "scope": "applicant_mahalla"
  },
  "sla_hours": 168,
  "escalation": {
    "after_hours": 240,
    "to": {"type": "department", "id": "tuman_kredit_bolimi"}
  }
}
```

**Default qoidalar:**
1. Mahalla darajasidagi muammolar — mahalla yettiligi
2. Tumanga oid (yer, katta kredit) — tuman bo'limi
3. Viloyat darajasidagi (mintaqaviy strategiya) — viloyat bo'limi
4. Respublika bandlari — respublikadagi tegishli vazirlik (eskalatsiya)

#### **5.2.5 SLA monitoring**

- Har murojaat uchun deadline (kategoriyaga ko'ra)
- Belgilangan vaqt o'tib ketsa — avtomatik eskalatsiya
- Hokim panelida "Diqqat!" widget — muddati o'tgan murojaatlar
- Daily digest: SMS yoki email rahbarga

#### **5.2.6 Statistika va dashboardlar**

**Hokim uchun:**
- **Heat-map**: mahalla × kategoriya (qaysi mahallada qaysi muammo ko'p)
- **Trend chart**: kategoriya × vaqt (oxirgi 6 oy)
- **SLA compliance**: bo'lim va yettilik bo'yicha
- **Qoniqish reytingi**: yettilik bo'yicha rating
- **Top 10 takrorlanuvchi muammolar**

**Tuman admin uchun:**
- Backlog (kelayotgan murojaatlar oqimi)
- Yettiliklar bo'yicha o'rtacha javob vaqti
- A'zolarning aktivligi

**Yettilik uchun:**
- Kelgan murojaatlar
- Hal qilingan/Kutayotgan
- O'rtacha javob vaqti
- Fuqaro reytingi

---

## 6. AI ishlash misollar

### 6.1 Yangi murojaat keldi — to'liq oqim

**Murojaat:** "Salom, men Otaboyeva Madina, Хонка туман, Чор-олдин маҳалласида яшайман. Менга 30 миллион сўм микрокредит керак, тикувчилик устахонаси очмоқчиман."

**AI taqdim etadi:**
```
1. Triage:
   ✓ Kategoriya: KREDIT (confidence: 0.94)
   ✓ Sub: Mikrokredit / Biznes-start (confidence: 0.89)
   ✓ Ustuvorlik: ODDIY (confidence: 0.91)
   ✓ Hudud: Xonqa tuman, Chor-oldin mahalla
   ✓ Yo'naltirish: Chor-oldin mahalla yettiligi
   ✓ SLA: 7 kun (kichik kredit uchun)

2. O'xshash 5 ta o'tgan ish:
   - #2024-1532: Karimova M. — tasdiqlangan, bank ajratdi
   - #2024-2104: Yusupova D. — rad, tegishli hujjat yo'q
   - #2025-0034: Soliyeva F. — tasdiqlangan
   - ...

3. Duplicate detection: yo'q (similarity max 0.71)

4. Tavsiya etilgan keyingi qadamlar:
   - Mahalla yettiligi — 7 kun ichida ko'rib chiqish
   - Talab qilinadigan hujjatlar: pasport, JSHSHIR, biznes reja
   - Jamlangan amaliy ko'mak: "Yoshlar bandligi" jamg'armasi

5. Avto-javob (draft):
   "Hurmatli Madina opa, sizning murojaatingiz qabul qilindi. 
   Murojaatingiz Chor-oldin mahalla yettiligida 7 kun ichida 
   ko'rib chiqiladi. Iltimos, quyidagi hujjatlarni tayyorlang:
   1. Pasport nusxasi
   2. JSHSHIR
   3. Biznes-reja..."
```

### 6.2 Anomaly detection misol

```
Eslatma: "Хива тумани да 1 хафта ичида КРЕДИТ турдаги мурожаатлар 
3 баробар кўпайди (одатдаги 12 та урнига 38 та). 
Маслаҳат: соҳа муаммолари бўлиши мумкин — текшириш зарур."
```

### 6.3 Hokim uchun haftalik resume

```
Joriy hafta natijasi (2026-04-01 dan 2026-04-08):

📊 JAMI:
- 234 ta yangi murojaat
- 198 ta hal qilindi
- O'rtacha javob: 42 soat

🔝 ENG KOʻP MUAMMOLAR:
1. Mikrokredit (67) — Xonqa, Bog'ot
2. Tomorqa yer (42) — Hazorasp
3. Ish o'rni (34) — Urganch sh.

⚠️ DIQQAT:
- 12 ta murojaat SLA dan oshib ketdi
- Yangi tendensiya: "Ipoteka kredit" — 2 hafta avval 0 edi, hozir 18 ta
```

---

## 7. Phase'lar bo'yicha rivojlanish

### Phase 1 (4 hafta) — Asos
- DB migrations (15 ta jadval)
- Modellar va policy'lar
- Murojaat CRUD
- Mahalla yettiligi modul
- Oddiy routing (rules orqali, AI'siz)

### Phase 2 (3 hafta) — AI Foundation
- pgvector setup yoki Qdrant
- Embedding pipeline (multilingual-e5)
- Asosiy classification (zero-shot LLM)
- Vector similarity search
- AI classification natijalarini saqlash

### Phase 3 (3 hafta) — Smart features
- Hybrid search (BM25 + vector + rerank)
- Duplicate detection
- AI draft generation (RAG)
- Anomaly detection (kunlik batch)

### Phase 4 (2 hafta) — Statistika va Dashboard
- Heat-map (mahalla × kategoriya)
- Trend charts
- SLA compliance dashboard
- Hokim haftalik resume

### Phase 5 (3 hafta) — Multi-channel
- Telegram bot intake
- Voice-to-text (Whisper)
- OCR uchun qog'oz arizalar
- SMS notification
- Mobil PWA

### Phase 6 (2 hafta) — Quality va deploy
- E2E test (security: cross-tenant)
- Load test (1000+ murojaat/kun)
- AI accuracy evaluation
- Production deploy

**Jami:** **17 hafta** (~4 oy)

---

## 8. Texnologiya stack qo'shimchalari

| Komponent | Tanlangan |
|---|---|
| Vector DB | PostgreSQL + pgvector (yoki Qdrant) |
| Embedding model | multilingual-e5-large |
| LLM (kategoriya) | GPT-4-mini yoki Claude Haiku |
| LLM (draft) | GPT-4 yoki Claude Sonnet |
| Speech-to-text | Whisper API |
| OCR | Tesseract + GPT-4 vision |
| Queue | Redis + Laravel Horizon |
| WebSocket | Laravel Reverb (real-time notify) |
| Telegram bot | Long polling + Laravel command |
| SMS gateway | Eskiz / Playmobile |
| Maplar | OpenStreetMap + Leaflet (heat-map) |

---

## 9. Xavfsizlik va maxfiylik

### 9.1 Shaxsiy ma'lumotlar
- JSHSHIR — encrypted (mavjud pattern)
- Tug'ilgan sana, manzil — encrypted
- Voice/photo — private storage, faqat tegishli yettilik a'zolariga

### 9.2 AI maxfiylik
- LLM ga yuborilgan har so'rov — JSHSHIR/passport raqami **olib tashlanadi** (PII redaction)
- Ovoz fayllar — 90 kundan keyin avtomatik o'chiriladi
- AI logs — tenant'ga bog'langan, audit'da ko'rinadi

### 9.3 Tenant izolyatsiya
- AI vector search query — har vaqt `hokimlik_id` filter bilan
- Cross-tenant data leak'ka qarshi unit testlar
- LLM ga 1 ta murojaatdan ortiq tarkib yuborilmaydi (cross-tenant info bermaslik uchun)

---

## 10. Risk va yumshatish

| Risk | Ehtimollik | Yumshatish |
|---|---|---|
| AI kategoriya xatosi (15-20%) | Yuqori | Manual review queue, real-time correction loop |
| Past resurslar (LLM cost) | O'rta | Caching + fallback to local Llama |
| Internet uzilishi (offline) | O'rta | PWA + local queue, sync keyinroq |
| Yoshlar tizimga qiziqmaslik | Yuqori | Telegram bot — UI'siz oddiy |
| Mahalla yettiligi raqamli savodsizligi | Yuqori | Oddiy mobil interface, ovozli buyruqlar |
| Ma'lumotlar to'planmaganligi | O'rta | Eski qog'oz arizalar OCR |

---

## 11. Aniq KPI ko'rsatkichlari

### Tizimga kiritilgandan keyin 3 oy ichida:
- ≥ 1000 ta murojaat tizimga kiritilgan
- ≥ 70% murojaatlar **mahalla darajasida** hal qilingan
- ≥ 85% AI kategorizatsiya aniqligi
- < 24 soat — birinchi javob (50-percentile)
- < 30 kun — to'liq hal qilish (kredit/yer)
- ≥ 4.0 / 5 — fuqaro qoniqish indeksi

### 6 oy ichida:
- Trend tahlili — 3 ta kuchli "siyosiy" muammo aniqlanadi
- Anomaly detection 95% aniqlik bilan ishlaydi
- AI draft generation — 60% holatlarda operator qabul qiladi (minor edit'lar bilan)

---

## 12. Tasdiqlash mezonlari (DoD)

Loyiha tugagan deb hisoblanadi, agar:

- [ ] Yoshlar uchrashuvi modulida 50+ uchrashuv yozib olingan
- [ ] 1000+ murojaat tizimga kiritilgan va AI tahlil qilgan
- [ ] 14 ta mahalla yettiligi konfiguratsiya qilingan va qaror qabul qilgan
- [ ] Vector search 100ms ostida ishlaydi
- [ ] Tenant izolyatsiya penetration test'dan o'tdi
- [ ] Hokim haftalik resume avtomatik yuboriladi
- [ ] Telegram bot 24/7 ishlaydi
- [ ] SLA dashboard real-time
- [ ] AI accuracy bo'yicha 3 oy davomida monitoring olib borildi
- [ ] Operator qo'llanmasi yozildi va o'qitish o'tkazildi

---

## 13. Mantiqiy tuzilma — bir qarashda

```
┌──────────────────────────────────────────────────────────┐
│  YOSHLAR (CITIZENS)                                       │
│  Web/Telegram/Voice/Qog'oz                                │
└────────────────────────┬─────────────────────────────────┘
                         │ MUROJAAT
                         ▼
┌──────────────────────────────────────────────────────────┐
│  AI TRIAGE (real-time, < 5s)                              │
│  - Kategoriya, ustuvorlik                                 │
│  - O'xshash o'tgan ishlar                                 │
│  - Duplicate check                                        │
└────────────────────────┬─────────────────────────────────┘
                         │
                         ▼
┌──────────────────────────────────────────────────────────┐
│  ROUTING ENGINE                                            │
│  Default: mahalla yettiligi                               │
│  Alternative: tuman bo'limi (escalation)                  │
└────────────────────────┬─────────────────────────────────┘
                         │
        ┌────────────────┼────────────────┐
        ▼                ▼                ▼
   ┌─────────┐     ┌──────────┐     ┌──────────┐
   │ MAHALLA │     │ TUMAN    │     │ VILOYAT  │
   │ YETTILIK│     │ BOʻLIM   │     │ DEPT     │
   │ (70%)   │     │ (25%)    │     │ (5%)     │
   └────┬────┘     └────┬─────┘     └────┬─────┘
        │               │                 │
        └───────────────┼─────────────────┘
                        │ QAROR
                        ▼
┌──────────────────────────────────────────────────────────┐
│  AI DRAFT GENERATION → OPERATOR REVIEW → SEND TO CITIZEN │
└────────────────────────┬─────────────────────────────────┘
                         │
                         ▼
┌──────────────────────────────────────────────────────────┐
│  CITIZEN FEEDBACK + SENTIMENT ANALYSIS                    │
└────────────────────────┬─────────────────────────────────┘
                         │
                         ▼
┌──────────────────────────────────────────────────────────┐
│  ANALYTICS (HOKIM, TUMAN, MAHALLA DASHBOARDS)             │
│  - Heat-map, Trend, KPI, Anomaly                          │
└──────────────────────────────────────────────────────────┘
```

---

## Ilova A: Schema example (yer murojaati)

```json
{
  "appeal_id": "uuid-...",
  "applicant": {
    "name": "Karimova Madina",
    "phone": "+998901234567",
    "jshshir": "[encrypted]",
    "birth_date": "1995-05-12"
  },
  "location": {
    "tuman": "Xonqa",
    "mahalla": "Chor-oldin",
    "address": "Mustaqillik ko'chasi 12-uy"
  },
  "category": "yer",
  "sub_category": "tomorqa",
  "body": "Tomorqa yer ajratish so'rovi...",
  "amount_hectares": 0.5,
  "ai_analysis": {
    "category_confidence": 0.94,
    "priority": "normal",
    "similar_cases": ["uuid-1", "uuid-2", "uuid-3"],
    "duplicate_check": null,
    "predicted_sla_days": 30
  },
  "routing": {
    "current": {"type": "council", "id": 42, "name": "Chor-oldin mahalla yettiligi"},
    "history": []
  },
  "status": "in_review",
  "documents": [...],
  "comments": [...]
}
```

---

## Ilova B: AI prompts (key examples)

### B.1 Klassifikatsiya prompt

```
System: Sen O'zbekistonda yoshlar murojaatlarini kategoriyalarga ajratadigan klassifikator'san.

Mavjud kategoriyalar:
- kredit (mikro, ipoteka, biznes, sotsial)
- yer (tomorqa, dehqonchilik, ishlab-chiqarish)
- ish (kasbga o'qitish, ish topish, biznes ochish)
- talim (universitet, granty, magistratura)
- ijtimoiy (tibbiy, nogironlik, ona-bola)
- huquqiy (hujjat, nikoh, mehnat)

User input: "{appeal_text}"

JSON formatida javob ber:
{
  "category": "...",
  "sub_category": "...",
  "priority": "low|normal|high|urgent",
  "confidence": 0-1,
  "reasoning": "..."
}
```

### B.2 Draft generation prompt (RAG)

```
System: Sen mahalla yettiligi xodimi'sansan. Quyidagi murojaatga rasmiy javob loyihasini tayyorla.

Murojaat: "{appeal_text}"

O'xshash o'tgan ishlar (kontekst):
{similar_cases_summary}

Mahalla qarori: {decision}

Talab: rasmiy ohangda, hurmat bilan, aniq qadamlar bilan javob bering.
```

---

**Hujjat tugadi.**
