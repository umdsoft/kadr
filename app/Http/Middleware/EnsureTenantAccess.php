<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Support\Tenant\TenantContext;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Joriy request uchun tenant kontekstini o'rnatish.
 *
 * Logika:
 *  - Auth talab — middleware'ning oldida `auth` middleware bo'lishi kerak
 *  - Super-admin / viloyat-admin — global rejimda (barcha tenantlar)
 *  - Super-admin uchun ?tenant=N orqali tanlangan tenant'ga kirish
 *  - Tuman-admin va boshqa rollar — faqat o'z hokimligi
 */
class EnsureTenantAccess
{
    public function __construct(private TenantContext $context) {}

    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        if (! $user) {
            return $next($request);
        }

        $isCrossTenantAdmin = $user->hasRole('super-admin') || $user->hasRole('viloyat-admin');

        // Super-admin uchun ?tenant=N override
        if ($isCrossTenantAdmin) {
            $requestedTenant = $request->query('tenant');
            if ($requestedTenant !== null && $requestedTenant !== '') {
                $this->context->set((string) $requestedTenant, isGlobal: false);
            } else {
                $this->context->set(null, isGlobal: true);
            }
        } else {
            // Oddiy foydalanuvchi — faqat o'z tenantida
            $this->context->set($user->hokimlik_id, isGlobal: false);
        }

        return $next($request);
    }
}
