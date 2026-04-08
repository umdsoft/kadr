<?php

declare(strict_types=1);

namespace App\Actions\Relatives;

use App\Models\Employee;
use App\Services\DeceasedFormatterService;

/**
 * Яқин қариндошлар ёзувларини сақлаш (sync).
 * Вафот этган қариндош учун иш жойи автоматик форматланади.
 */
class SaveRelativesAction
{
    public function __construct(
        private DeceasedFormatterService $deceasedFormatter,
    ) {}

    /**
     * @param  array<int, array<string, mixed>>  $items
     */
    public function execute(Employee $employee, array $items): void
    {
        $employee->relatives()->delete();

        foreach ($items as $item) {
            // Вафот этган — автоматик формат
            if (! empty($item['is_deceased'])) {
                $workplaceAndPosition = $this->deceasedFormatter->format(
                    (int) $item['deceased_year'],
                    $item['former_position'] ?? '',
                );
            } else {
                $workplaceAndPosition = $item['workplace_and_position'];
            }

            $employee->relatives()->create([
                'relationship_type' => $item['relationship_type'],
                'full_name_cyr' => $item['full_name_cyr'],
                'birth_year' => $item['birth_year'],
                'birth_place' => $item['birth_place'],
                'is_deceased' => $item['is_deceased'] ?? false,
                'deceased_year' => $item['deceased_year'] ?? null,
                'workplace_and_position' => $workplaceAndPosition,
                'residence_full' => $item['residence_full'],
            ]);
        }
    }
}
