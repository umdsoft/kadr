<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Spatie\Permission\Models\Role as SpatieRole;

/**
 * UUID primary key uchun Spatie Role'ni kengaytirish.
 */
class Role extends SpatieRole
{
    use HasUuids;
}
