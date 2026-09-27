<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Department;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/**
 * Super-admin / viloyat-admin uchun tenant switcher.
 * Tanlash session'ga emas, URL'ga yoziladi (`?tenant=N`).
 */
class TenantController extends Controller
{
    public function switch(Request $request): RedirectResponse
    {
        abort_unless(
            $request->user()?->hasRole('super-admin') || $request->user()?->hasRole('viloyat-admin'),
            403,
        );

        $tenantId = $request->validate([
            'tenant_id' => ['nullable', 'string', 'exists:departments,id'],
            'redirect_to' => ['nullable', 'string'],
        ])['tenant_id'] ?? null;

        // Tenant top-level bo'lishi tekshiruvi
        if ($tenantId !== null) {
            $isTenant = Department::query()->whereNull('parent_id')->where('id', $tenantId)->exists();
            abort_unless($isTenant, 422, 'Bu hokimlik tenant emas');
        }

        $redirectTo = $request->input('redirect_to', '/');
        $separator = str_contains($redirectTo, '?') ? '&' : '?';
        $url = $tenantId === null
            ? $redirectTo
            : "{$redirectTo}{$separator}tenant={$tenantId}";

        return redirect()->to($url);
    }
}
