<?php

declare(strict_types=1);

use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * KBT mavjud userlarini markaziy `auth.users`ga ko'chirish (BIR XIL id bilan).
 * Shunda KBT kodi (rollar/org/dept — hammasi id bo'yicha) tegilmasdan ishlaydi,
 * faqat kredential (login/parol/is_active) markazga ko'chadi.
 * Har userga xbt tizimiga ruxsat (user_system_access) beriladi.
 * Idempotent: updateOrInsert.
 */
return new class extends Migration
{
    public function up(): void
    {
        // Markaziy auth faqat PostgreSQL muhitida. SQLite testlarda skip.
        if (config('database.default') !== 'pgsql') {
            return;
        }

        $auth = DB::connection('auth');

        $xbtSystemId = $auth->table('systems')->where('code', 'xbt')->value('id');
        if ($xbtSystemId === null) {
            $xbtSystemId = (string) Str::uuid();
            $auth->table('systems')->insert([
                'id' => $xbtSystemId, 'code' => 'xbt',
                'name' => 'Кадрлар бошқарув тизими (KBT)', 'is_active' => true,
                'sort_order' => 1, 'created_at' => now(), 'updated_at' => now(),
            ]);
        }

        // Soft-delete qilinganlar ham ko'chsin (is_active=false bo'lib)
        User::withTrashed()->with('roles')->chunkById(200, function ($users) use ($auth, $xbtSystemId) {
            foreach ($users as $u) {
                $auth->table('users')->updateOrInsert(
                    ['id' => $u->id],
                    [
                        'login' => $u->login,
                        'password' => $u->password,
                        'name' => $u->name,
                        'phone' => null,
                        'is_active' => $u->deleted_at === null,
                        'remember_token' => $u->remember_token,
                        'deleted_at' => $u->deleted_at,
                        'created_at' => $u->created_at ?? now(),
                        'updated_at' => now(),
                    ],
                );

                $auth->table('user_system_access')->updateOrInsert(
                    ['user_id' => $u->id, 'system_id' => $xbtSystemId],
                    [
                        'id' => (string) Str::uuid(),
                        'role' => $u->getRoleNames()->first(),
                        'is_active' => $u->deleted_at === null,
                        'updated_at' => now(),
                        'created_at' => now(),
                    ],
                );
            }
        });
    }

    public function down(): void
    {
        // Ma'lumot backfill'i — orqaga qaytarish auth.users'ni tozalamaydi
        // (boshqa tizimlar bog'liq bo'lishi mumkin). Qo'lda boshqariladi.
    }
};
