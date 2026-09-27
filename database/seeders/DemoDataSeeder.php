<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\ControlPlan;
use App\Models\ControlPlanItem;
use App\Models\Department;
use App\Models\Employee;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * Намунавий (demo) маълумотлар — фақат синов/демонстрация учун.
 * DatabaseSeeder'га уланмаган. Ишга тушириш:
 *   php artisan db:seed --class=DemoDataSeeder
 *
 * Dashboard статистикаси нолдан фарқли бўлиши учун бир нечта ходим ва
 * назорат режа яратади (ҳар бирига hokimlik_id аниқ берилади — CLI'да
 * TenantContext бўш бўлгани сабабли).
 */
class DemoDataSeeder extends Seeder
{
    public function run(): void
    {
        $hokimliklar = Department::whereNull('parent_id')
            ->orderBy('id')
            ->take(3)
            ->get();

        $admin = User::where('login', 'admin')->first();

        foreach ($hokimliklar as $index => $hokimlik) {
            // Ҳар ҳокимликка 3-5 ходим
            $count = 3 + $index;
            Employee::factory()
                ->count($count)
                ->create(['hokimlik_id' => $hokimlik->id]);

            // Ҳар ҳокимликка 1-2 назорат режа
            for ($i = 1; $i <= 1 + $index; $i++) {
                ControlPlan::create([
                    'title' => "{$hokimlik->name_cyr} — {$i}-сонли назорат режа",
                    'document_number' => sprintf('NR-%d-%03d', $hokimlik->id, $i),
                    'document_date' => '2026-01-15',
                    'status' => $i === 1 ? 'active' : 'completed',
                    'status_date' => '2026 йил 15 январ ҳолатига',
                    'created_by' => $admin?->id,
                    'hokimlik_id' => $hokimlik->id,
                ]);
            }
        }

        // ===== Котибият мудири → ташкилот → org-admin → топшириқ оқими =====
        $hokimlik = $hokimliklar->first();
        $kompleks = Department::where('type', 'kompleks')->where('parent_id', $hokimlik->id)->first();

        if ($hokimlik && $kompleks) {
            $mudir = User::create([
                'name' => 'Котибият мудири (демо)',
                'login' => 'kotibyat',
                'password' => Hash::make('Parol@2026!'),
                'department_id' => $kompleks->id,
            ]);
            $mudir->assignRole('kotibyat-mudiri');

            // 2 ta ташкилот
            $orgs = collect(['1-сон умумтаълим мактаби', 'Туман марказий поликлиникаси'])
                ->map(fn ($name, $i) => Organization::create([
                    'hokimlik_id' => $hokimlik->id,
                    'kompleks_id' => $kompleks->id,
                    'name_cyr' => $name,
                    'inn' => '30'.str_pad((string) ($i + 1), 7, '0', STR_PAD_LEFT),
                    'phone' => '+998620000'.($i + 1),
                    'created_by' => $mudir->id,
                ]));

            // Биринчи ташкилотга admin login
            $firstOrg = $orgs->first();
            $orgAdmin = User::create([
                'name' => 'Мактаб директори (демо)',
                'login' => 'maktab',
                'password' => Hash::make('Parol@2026!'),
                'organization_id' => $firstOrg->id,
            ]);
            $orgAdmin->assignRole('tashkilot-admin');

            // Ташкилотга мустақил топшириқ
            $task = ControlPlanItem::create([
                'source' => 'standalone',
                'hokimlik_id' => $hokimlik->id,
                'kompleks_id' => $kompleks->id,
                'created_by' => $mudir->id,
                'title' => 'Мактаб ҳудудини ободонлаштириш',
                'task_description' => 'Мактаб ҳудудини тозалаш, кўкаламзорлаштириш ва жиҳозлаш.',
                'deadline' => '2026-09-01',
                'execution_status' => 'in_progress',
            ]);
            $task->responsibles()->create([
                'assignee_type' => 'organization',
                'assignee_id' => $firstOrg->id,
                'responsible_name' => $firstOrg->name_cyr,
                'is_primary' => true,
            ]);
        }

        $this->command->info('Demo: '.Employee::withoutTenantScope()->count().' ходим, '
            .ControlPlan::withoutTenantScope()->count().' назорат режа, '
            .Organization::withoutTenantScope()->count().' ташкилот яратилди.');
        $this->command->info('Demo логинлар: kotibyat / maktab (парол: Parol@2026!)');
    }
}
