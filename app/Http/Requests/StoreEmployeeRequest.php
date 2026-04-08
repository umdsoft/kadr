<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Services\ValidationRulesService;
use Illuminate\Foundation\Http\FormRequest;

class StoreEmployeeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('employee.create') ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return app(ValidationRulesService::class)->employeeRules();
    }
}
