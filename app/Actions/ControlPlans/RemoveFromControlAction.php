<?php

declare(strict_types=1);

namespace App\Actions\ControlPlans;

use App\Models\ControlPlanItem;
use App\Models\User;

/**
 * Топшириқни назоратдан ечиш / қайта назоратга қайтариш.
 */
class RemoveFromControlAction
{
    public function remove(ControlPlanItem $item, User $by, ?string $reason = null): ControlPlanItem
    {
        $item->update([
            'control_removed_at' => now(),
            'control_removed_by' => $by->id,
            'control_removal_reason' => $reason,
        ]);

        return $item;
    }

    public function restore(ControlPlanItem $item): ControlPlanItem
    {
        $item->update([
            'control_removed_at' => null,
            'control_removed_by' => null,
            'control_removal_reason' => null,
        ]);

        return $item;
    }
}
