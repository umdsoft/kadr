<?php

declare(strict_types=1);

namespace App\Search\Filters;

use Illuminate\Database\Eloquent\Builder;

/**
 * Туғилган тумани бўйича фильтр.
 */
class DistrictFilter implements FilterInterface
{
    public function apply(Builder $query, mixed $value): Builder
    {
        return $query->where('birth_district_id', (int) $value);
    }
}
