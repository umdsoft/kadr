<?php

declare(strict_types=1);

namespace App\Search\Filters;

use Illuminate\Database\Eloquent\Builder;

/**
 * Миллат бўйича фильтр.
 */
class NationalityFilter implements FilterInterface
{
    public function apply(Builder $query, mixed $value): Builder
    {
        return $query->where('nationality', (string) $value);
    }
}
