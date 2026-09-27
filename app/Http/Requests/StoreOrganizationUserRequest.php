<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreOrganizationUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Asosiy avtorizatsiya OrganizationPolicy::manageUsers orqali (controllerda).
        return $this->user()?->can('tashkilot.manage-users') ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'login' => ['required', 'string', 'max:100', 'regex:/^[a-zA-Z0-9._-]+$/', 'unique:users,login'],
            'password' => ['required', 'string', 'min:8'],
            // Faqat tashkilot rollari
            'role' => ['required', 'string', 'in:tashkilot-admin,tashkilot-xodimi'],
        ];
    }
}
