<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Department;
use App\Models\Position;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use RuntimeException;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        // Профил тўлиқ бўлиши учун — биринчи ҳокимлик (top-level) ва super-admin лавозими.
        $hokimlik = Department::whereNull('parent_id')->orderBy('id')->first();
        $position = Position::where('role_name', 'super-admin')->first()
            ?? Position::orderBy('id')->first();

        $password = $this->resolvePassword();

        // updateOrCreate — қайта seed қилинганда дубликат/уникал хатолиги бўлмайди.
        $admin = User::updateOrCreate(
            ['login' => 'admin'],
            [
                'name' => 'Super Admin',
                'email' => 'admin@kbt.uz',
                'password' => Hash::make($password),
                'department_id' => $hokimlik?->id,
                'position_id' => $position?->id,
            ],
        );

        $admin->syncRoles(['super-admin']);
    }

    /**
     * Бошланғич admin пароли — фақат .env дан (ADMIN_SEED_PASSWORD).
     * Кодга ёзилган парол хавфсизлик тешиги — шунинг учун production да
     * .env да бўлмаса seed тўхтайди.
     */
    private function resolvePassword(): string
    {
        $password = (string) env('ADMIN_SEED_PASSWORD', '');

        if ($password !== '') {
            return $password;
        }

        if (app()->environment('production')) {
            throw new RuntimeException(
                'ADMIN_SEED_PASSWORD .env да ўрнатилмаган. Production да admin паролини кодга ёзиб бўлмайди.',
            );
        }

        // Фақат local/dev учун — очиқ огоҳлантириш билан.
        $this->command?->warn('ADMIN_SEED_PASSWORD топилмади — dev учун вақтинчалик парол ишлатилди. Кириб дарҳол алмаштиринг!');

        return 'ChangeMe!'.bin2hex(random_bytes(4));
    }
}
