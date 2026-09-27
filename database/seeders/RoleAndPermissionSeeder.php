<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;
use Spatie\Permission\PermissionRegistrar;

/**
 * Multi-tenant rollar va ruxsatlar.
 *
 * 3 tabaqa:
 *  - Cross-tenant (super-admin, viloyat-admin) — barcha hokimliklar
 *  - Tenant-level (tuman-admin) — bitta hokimlik to'liq boshqaruv
 *  - User-level (mutaxassis va boshqalar) — modulga qarab cheklangan
 */
class RoleAndPermissionSeeder extends Seeder
{
    public function run(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // ===== Permissionlar =====

        $permissions = [
            // Tenant-aware
            'tenant.view-all',           // Barcha tenantlarni ko'rish (cross-tenant)
            'tenant.switch',             // Tenant context'ni o'zgartirish (super-admin/viloyat-admin)
            'tenant.manage-users',       // O'z tenanti foydalanuvchilarini yaratish/o'zgartirish

            // Kadrlar moduli
            'kadrlar.view', 'kadrlar.create', 'kadrlar.update', 'kadrlar.delete', 'kadrlar.export',

            // Chora-tadbirlar moduli
            'tadbirlar.view', 'tadbirlar.create', 'tadbirlar.update', 'tadbirlar.delete',

            // Hokim yordamchilari moduli
            'hokim-yordamchilari.view',
            'hokim-yordamchilari.create',
            'hokim-yordamchilari.update',
            'hokim-yordamchilari.delete',

            // Yoshlar yetakchilari moduli
            'yoshlar.view',
            'yoshlar.create',
            'yoshlar.update',
            'yoshlar.delete',

            // Yoshlar uchrashuvlari moduli
            'meetings.view',
            'meetings.create',
            'meetings.update',
            'meetings.delete',

            // Murojaatlar moduli
            'appeals.view',
            'appeals.view-all',     // tenant ichida boshqa mahallani ham ko'rish
            'appeals.create',
            'appeals.update',
            'appeals.delete',
            'appeals.assign',       // routing/transfer
            'appeals.decide',       // qaror chiqarish (yettilik)
            'appeals.export',

            // Mahalla yettiligi moduli
            'councils.view',
            'councils.manage',      // yettilik tarkibini boshqarish

            // Tashkilotlar moduli (kotibyat mudiri yaratadi)
            'tashkilotlar.view',
            'tashkilotlar.create',
            'tashkilotlar.update',
            'tashkilotlar.delete',
            'tashkilot.manage-users',   // tashkilot admin/jamoa loginlarini ochish

            // Topshiriqlar (umumiy — tashkilotga biriktirish + org tomonidan ko'rish/hisobot)
            'topshiriqlar.assign-org',  // topshiriqni tashkilotga biriktirish
            'topshiriqlar.view-own',    // tashkilot o'ziga kelgan topshiriqlarni ko'radi
            'topshiriqlar.report',      // tashkilot ijro hisobotini kiritadi

            // AI moduli
            'ai.use',               // AI taklif olish
            'ai.review',            // AI natijalarini ko'rib chiqish (audit)

            // Tizim
            'user.view', 'user.create', 'user.update', 'user.delete',
            'audit.view',
            'dashboard.view',
            'dashboard.cross-tenant', // Barcha tenantlar bo'yicha agregatsiya
        ];

        foreach ($permissions as $name) {
            Permission::firstOrCreate(['name' => $name]);
        }

        // ===== Rollar =====

        // 1. SUPER-ADMIN — Gate::before orqali avtomatik barcha permissionlar
        Role::firstOrCreate(['name' => 'super-admin']);

        // 2. VILOYAT-ADMIN — barcha tenantlarni ko'radi, viloyatda to'liq boshqaruv
        $viloyat = Role::firstOrCreate(['name' => 'viloyat-admin']);
        $viloyat->givePermissionTo([
            'tenant.view-all', 'tenant.switch', 'tenant.manage-users',
            'kadrlar.view', 'kadrlar.create', 'kadrlar.update', 'kadrlar.delete', 'kadrlar.export',
            'tadbirlar.view', 'tadbirlar.create', 'tadbirlar.update', 'tadbirlar.delete',
            'hokim-yordamchilari.view',
            'yoshlar.view',
            'meetings.view', 'meetings.create', 'meetings.update',
            'appeals.view', 'appeals.view-all', 'appeals.assign', 'appeals.export',
            'councils.view', 'councils.manage',
            'tashkilotlar.view',
            'ai.use', 'ai.review',
            'user.view', 'user.create', 'user.update', 'user.delete',
            'audit.view',
            'dashboard.view', 'dashboard.cross-tenant',
        ]);

        // 3. TUMAN-ADMIN — o'z hokimligida to'liq boshqaruv
        $tumanAdmin = Role::firstOrCreate(['name' => 'tuman-admin']);
        $tumanAdmin->givePermissionTo([
            'tenant.manage-users',
            'kadrlar.view', 'kadrlar.create', 'kadrlar.update', 'kadrlar.delete', 'kadrlar.export',
            'tadbirlar.view', 'tadbirlar.create', 'tadbirlar.update', 'tadbirlar.delete',
            'hokim-yordamchilari.view', 'hokim-yordamchilari.create', 'hokim-yordamchilari.update', 'hokim-yordamchilari.delete',
            'yoshlar.view', 'yoshlar.create', 'yoshlar.update', 'yoshlar.delete',
            'meetings.view', 'meetings.create', 'meetings.update', 'meetings.delete',
            'appeals.view', 'appeals.view-all', 'appeals.create', 'appeals.update', 'appeals.assign', 'appeals.export',
            'councils.view', 'councils.manage',
            'tashkilotlar.view', 'tashkilotlar.create', 'tashkilotlar.update', 'tashkilotlar.delete',
            'tashkilot.manage-users', 'topshiriqlar.assign-org',
            'ai.use', 'ai.review',
            'user.view', 'user.create', 'user.update',
            'audit.view',
            'dashboard.view',
        ]);

        // 4. HOKIM MASLAHATCHISI — kuzatuv darajasida
        $hokimMaslahatchisi = Role::firstOrCreate(['name' => 'hokim-maslahatchisi']);
        $hokimMaslahatchisi->givePermissionTo([
            'kadrlar.view',
            'tadbirlar.view',
            'hokim-yordamchilari.view',
            'yoshlar.view',
            'audit.view',
            'dashboard.view',
        ]);

        // 5. HOKIM O'RINBOSARI — chora-tadbir va hokim yordamchilari boshqaruv
        $hokimOrinbosari = Role::firstOrCreate(['name' => 'hokim-orinbosari']);
        $hokimOrinbosari->givePermissionTo([
            'kadrlar.view',
            'tadbirlar.view', 'tadbirlar.create', 'tadbirlar.update',
            'hokim-yordamchilari.view', 'hokim-yordamchilari.create', 'hokim-yordamchilari.update',
            'yoshlar.view',
            'audit.view',
            'dashboard.view',
        ]);

        // 6. KOTIBIYAT MUDIRI — nazorat reja va topshiriqlarni boshqaradi,
        //    mavjud tashkilotlarga topshiriq biriktiradi.
        //    (Tashkilot YARATISH/boshqarish va Audit jurnal — admin vazifasi)
        $kotibyat = Role::firstOrCreate(['name' => 'kotibyat-mudiri']);
        $kotibyat->givePermissionTo([
            'tadbirlar.view', 'tadbirlar.create', 'tadbirlar.update', 'tadbirlar.delete',
            'topshiriqlar.assign-org',
            'dashboard.view',
        ]);

        // 7. AXBOROT TAHLIL GURUHI
        $axborot = Role::firstOrCreate(['name' => 'axborot-tahlil']);
        $axborot->givePermissionTo([
            'kadrlar.view',
            'tadbirlar.view',
            'hokim-yordamchilari.view',
            'yoshlar.view',
            'audit.view',
            'dashboard.view',
        ]);

        // 8. MUTAXASSIS — chora-tadbir va yoshlar bo'yicha ish
        $mutaxassis = Role::firstOrCreate(['name' => 'mutaxassis']);
        $mutaxassis->givePermissionTo([
            'tadbirlar.view', 'tadbirlar.create', 'tadbirlar.update',
            'yoshlar.view', 'yoshlar.create', 'yoshlar.update',
            'dashboard.view',
        ]);

        // 9. KADRLAR XODIMI
        $kadrlar = Role::firstOrCreate(['name' => 'kadrlar-xodimi']);
        $kadrlar->givePermissionTo([
            'kadrlar.view', 'kadrlar.create', 'kadrlar.update', 'kadrlar.delete', 'kadrlar.export',
            'dashboard.view',
        ]);

        // 10. TUMAN MUTAXASSISI — yangi rol (yoshlar yetakchilari uchun masalan)
        $tumanMutaxassis = Role::firstOrCreate(['name' => 'tuman-mutaxassis']);
        $tumanMutaxassis->givePermissionTo([
            'tadbirlar.view',
            'yoshlar.view', 'yoshlar.create', 'yoshlar.update',
            'meetings.view', 'meetings.create',
            'appeals.view', 'appeals.create', 'appeals.update',
            'ai.use',
            'dashboard.view',
        ]);

        // 11. MAHALLA YETTILIGI AʼZOSI — o'z mahallasi murojaatlarini ko'rish va qaror chiqarish
        $council = Role::firstOrCreate(['name' => 'mahalla-yettiligi']);
        $council->givePermissionTo([
            'appeals.view',
            'appeals.update',
            'appeals.decide',
            'councils.view',
            'meetings.view',
            'ai.use',
            'dashboard.view',
        ]);

        // 12. TASHKILOT ADMINI — o'z tashkiloti ma'lumoti, jamoasi va kelgan topshiriqlar
        $tashkilotAdmin = Role::firstOrCreate(['name' => 'tashkilot-admin']);
        $tashkilotAdmin->givePermissionTo([
            'tashkilot.manage-users',   // o'z tashkiloti jamoasini boshqarish
            'topshiriqlar.view-own',
            'topshiriqlar.report',
            'dashboard.view',
        ]);

        // 13. TASHKILOT XODIMI — o'ziga kelgan topshiriqlarni ko'rish va hisobot
        $tashkilotXodimi = Role::firstOrCreate(['name' => 'tashkilot-xodimi']);
        $tashkilotXodimi->givePermissionTo([
            'topshiriqlar.view-own',
            'topshiriqlar.report',
            'dashboard.view',
        ]);
    }
}
