<?php

declare(strict_types=1);

namespace App\Actions\HokimYordamchilari;

use App\Models\HokimYordamchisi;

class UpdateHyAction
{
    /** @param array<string, mixed> $data */
    public function execute(HokimYordamchisi $hy, array $data): HokimYordamchisi
    {
        $hy->update($data);

        return $hy->refresh();
    }
}
