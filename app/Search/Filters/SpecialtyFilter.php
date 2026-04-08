<?php

declare(strict_types=1);

namespace App\Search\Filters;

use Illuminate\Database\Eloquent\Builder;

/**
 * Мутахассислик бўйича фильтр.
 */
class SpecialtyFilter implements FilterInterface
{
    public function apply(Builder $query, mixed $value): Builder
    {
        return $query->where('specialty_by_education', 'like', "%{$value}%");
    }
}
