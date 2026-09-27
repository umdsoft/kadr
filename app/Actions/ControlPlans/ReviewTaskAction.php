<?php

declare(strict_types=1);

namespace App\Actions\ControlPlans;

use App\Enums\ReviewStatus;
use App\Models\ControlPlanItem;
use App\Models\User;

/**
 * Топшириқ ижросини тасдиқлаш оқими (EDO):
 *  - submit: ташкилот ижрони тасдиққа юборади
 *  - approve: котибият мудири тасдиқлайди
 *  - returnForRework: котибият мудири қайта ишлашга қайтаради
 */
class ReviewTaskAction
{
    public function submit(ControlPlanItem $item): void
    {
        $item->update([
            'review_status' => ReviewStatus::SUBMITTED->value,
            'submitted_at' => now(),
            'reviewed_at' => null,
            'reviewed_by' => null,
            'review_comment' => null,
        ]);
    }

    public function withdraw(ControlPlanItem $item): void
    {
        $item->update([
            'review_status' => null,
            'submitted_at' => null,
        ]);
    }

    public function approve(ControlPlanItem $item, User $by): void
    {
        $item->update([
            'review_status' => ReviewStatus::APPROVED->value,
            'reviewed_at' => now(),
            'reviewed_by' => $by->id,
            'execution_status' => 'completed',
        ]);
    }

    public function returnForRework(ControlPlanItem $item, User $by, ?string $comment): void
    {
        $item->update([
            'review_status' => ReviewStatus::RETURNED->value,
            'review_comment' => $comment,
            'reviewed_at' => now(),
            'reviewed_by' => $by->id,
            'execution_status' => 'in_progress',
        ]);
    }
}
