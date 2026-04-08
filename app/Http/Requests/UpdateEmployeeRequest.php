<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Services\ValidationRulesService;
use Illuminate\Foundation\Http\FormRequest;

class UpdateEmployeeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('employee.update') ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $employeeId = (int) $this->route('employee');

        return app(ValidationRulesService::class)->employeeRules($employeeId);
    }
}
