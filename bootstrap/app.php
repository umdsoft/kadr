<?php

declare(strict_types=1);

use App\Http\Middleware\EnsureTenantAccess;
use App\Http\Middleware\HandleInertiaRequests;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Inertia\Inertia;
use Symfony\Component\HttpFoundation\Response;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->web(append: [
            EnsureTenantAccess::class,
            HandleInertiaRequests::class,
        ]);

        // EnsureTenantAccess route-model binding'dan OLDIN ishlashi shart. Aks holda
        // {employee}/{task}/... bind qilinayotganda TenantContext hali bo'sh bo'lib,
        // BelongsToTenant global scope filtrlamay qoladi (HIGH-1 — himoya faqat
        // policy sameTenant'ga qolardi). Endi bind ham tenant bo'yicha scoped.
        $middleware->prependToPriorityList(
            before: SubstituteBindings::class,
            prepend: EnsureTenantAccess::class,
        );
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Брендланган хато саҳифалари (Inertia). 404/403/419/503 — ҳар доим,
        // 500 — фақат debug ўчиқ бўлганда (дастурчилар Ignition'ни кўриши учун).
        $exceptions->respond(function (Response $response, Throwable $e, Request $request) {
            $status = $response->getStatusCode();
            $branded = [403, 404, 419, 503];

            if (in_array($status, $branded, true) || ($status === 500 && ! config('app.debug'))) {
                return Inertia::render('Error', ['status' => $status])
                    ->toResponse($request)
                    ->setStatusCode($status);
            }

            return $response;
        });
    })->create();
