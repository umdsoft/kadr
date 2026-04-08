<?php

declare(strict_types=1);

namespace App\Search\Filters;

use Illuminate\Database\Eloquent\Builder;

/**
 * Ф.И.Ш. бўйича қидирув (Кирилл ва Лотин).
 */
class NameFilter implements FilterInterface
{
    public function apply(Builder $query, mixed $value): Builder
    {
        $search = (string) $value;

        return $query->where(function (Builder $q) use ($search) {
            $q->where('last_name_cyr', 'like', "%{$search}%")
                ->orWhere('first_name_cyr', 'like', "%{$search}%")
                ->orWhere('middle_name_cyr', 'like', "%{$search}%")
                ->orWhere('last_name_lat', 'like', "%{$search}%")
                ->orWhere('first_name_lat', 'like', "%{$search}%");
        });
    }
}
