<?php

declare(strict_types=1);

namespace App\Actions\WorkHistory;

use App\Models\Employee;
use App\Models\WorkHistory;

/**
 * Меҳнат фаолияти ёзувларини сақлаш (яратиш/янгилаш).
 * Бир марта бутун рўйхатни sync қилади — wizard формадан келади.
 */
class SaveWorkHistoryAction
{
    /**
     * @param  array<int, array<string, mixed>>  $items
     */
    public function execute(Employee $employee, array $items): void
    {
        // Мавжуд ёзувларни ўчириб, янгисини яратамиз (sync)
        $employee->workHistory()->delete();

        foreach ($items as $index => $item) {
            $employee->workHistory()->create([
                'start_year' => $item['start_year'],
                'end_year' => $item['end_year'] ?? null,
                'organization_full' => $item['organization_full'],
                'position_full' => $item['position_full'],
                'order_number' => $item['order_number'] ?? null,
                'order_date' => $item['order_date'] ?? null,
                'sort_order' => $index,
            ]);
        }
    }
}
