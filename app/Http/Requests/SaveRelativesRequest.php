<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Services\ValidationRulesService;
use Illuminate\Foundation\Http\FormRequest;

class SaveRelativesRequest extends FormRequest
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
        return app(ValidationRulesService::class)->relativesRules();
    }
}
