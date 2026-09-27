<?php

declare(strict_types=1);

namespace App\Providers;

use App\Actions\Fortify\CreateNewUser;
use App\Actions\Fortify\ResetUserPassword;
use App\Actions\Fortify\UpdateUserPassword;
use App\Actions\Fortify\UpdateUserProfileInformation;
use App\Models\User;
use App\Services\Auth\CentralIdentitySync;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Laravel\Fortify\Fortify;

class FortifyServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Fortify::createUsersUsing(CreateNewUser::class);
        Fortify::updateUserProfileInformationUsing(UpdateUserProfileInformation::class);
        Fortify::updateUserPasswordsUsing(UpdateUserPassword::class);
        Fortify::resetUserPasswordsUsing(ResetUserPassword::class);

        // MARKAZIY IDENTIFIKATSIYA: pgsql (dev/prod) — auth.users'dan tekshiradi,
        // xbt tizimiga ruxsatni tasdiqlaydi, KBT User (bir xil id) qaytaradi.
        // SQLite testlarda — standart lokal auth (public.users).
        Fortify::authenticateUsing(function (Request $request) {
            $login = (string) $request->input(Fortify::username());
            $password = (string) $request->input('password');

            if (config('database.default') !== 'pgsql') {
                // Test/sqlite: lokal auth
                $user = User::where('login', $login)->first();

                return ($user && Hash::check($password, $user->password)) ? $user : null;
            }

            $central = DB::connection('auth')->table('users')
                ->whereNull('deleted_at')
                ->where('login', $login)
                ->where('is_active', true)
                ->first();

            if (! $central || ! Hash::check($password, $central->password)) {
                return null;
            }

            // Shu tizimga (xbt) ruxsat bormi?
            $hasAccess = DB::connection('auth')->table('user_system_access as usa')
                ->join('systems as s', 's.id', '=', 'usa.system_id')
                ->where('usa.user_id', $central->id)
                ->where('usa.is_active', true)
                ->where('s.code', CentralIdentitySync::SYSTEM_CODE)
                ->where('s.is_active', true)
                ->exists();

            if (! $hasAccess) {
                return null;
            }

            DB::connection('auth')->table('users')
                ->where('id', $central->id)
                ->update(['last_login_at' => now()]);

            // KBT User (bir xil id) — barcha KBT kodi (rol/org/dept) shu bilan ishlaydi
            return User::find($central->id);
        });

        // Inertia орқали саҳифаларни кўрсатиш
        Fortify::loginView(fn () => Inertia::render('Auth/Login'));

        Fortify::twoFactorChallengeView(fn () => Inertia::render('Auth/TwoFactorChallenge'));

        // Диққат: registerView АТАЙЛАБ рўйхатдан ўтказилмаган — бу давлат тизимида
        // очиқ рўйхатдан ўтиш ёпиқ (config/fortify.php: registration disabled).
        // Фойдаланувчиларни фақат admin яратади (UserController).

        // Login rate limiter — 5 та уриниш/дақиқа
        RateLimiter::for('login', function (Request $request) {
            $throttleKey = Str::transliterate(
                Str::lower($request->input(Fortify::username())).'|'.$request->ip(),
            );

            return Limit::perMinute(5)->by($throttleKey);
        });

        // 2FA rate limiter
        RateLimiter::for('two-factor', function (Request $request) {
            return Limit::perMinute(5)->by($request->session()->get('login.id'));
        });
    }
}
