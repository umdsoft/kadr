<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Models\Employee;
use App\Services\ValidationRulesService;
use Illuminate\Foundation\Http\FormRequest;

class UpdateEmployeeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('kadrlar.update') ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        // Route-model binding'да {employee} — Employee модели; акс ҳолда хом id.
        $route = $this->route('employee');
        $employeeId = $route instanceof Employee ? $route->getKey() : (string) $route;

        return app(ValidationRulesService::class)->employeeRules($employeeId);
    }
}
