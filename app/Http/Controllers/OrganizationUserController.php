<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\StoreOrganizationUserRequest;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Hash;

/**
 * Ташкилот фойдаланувчилари (admin + жамоа) — котибият мудири очади/бошқаради.
 */
class OrganizationUserController extends Controller
{
    /** Ташкилотга admin/ходим логини яратиш. */
    public function store(StoreOrganizationUserRequest $request, Organization $organization): RedirectResponse
    {
        $this->authorize('manageUsers', $organization);

        $user = User::create([
            'name' => $request->validated('name'),
            'login' => $request->validated('login'),
            'password' => Hash::make($request->validated('password')),
            'organization_id' => $organization->id,
            // department_id / position_id — null (org foydalanuvchisi)
        ]);

        $user->assignRole($request->validated('role'));

        return back()->with('success', 'Ташкилот фойдаланувчиси яратилди.');
    }

    /** Ташкилот фойдаланувчисини ўчириш. */
    public function destroy(Organization $organization, User $user): RedirectResponse
    {
        $this->authorize('manageUsers', $organization);

        // Faqat shu tashkilotga tegishli foydalanuvchini o'chirish mumkin
        abort_unless($user->organization_id === $organization->id, 403);

        $user->delete();

        return back()->with('success', 'Фойдаланувчи ўчирилди.');
    }
}
