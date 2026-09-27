<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Spatie\Permission\Models\Permission as SpatiePermission;

/**
 * UUID primary key uchun Spatie Permission'ni kengaytirish.
 */
class Permission extends SpatiePermission
{
    use HasUuids;
}
