<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Department;
use App\Models\Organization;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Organization>
 */
class OrganizationFactory extends Factory
{
    protected $model = Organization::class;

    public function definition(): array
    {
        $hokimlik = Department::whereNull('parent_id')->inRandomOrder()->first();

        $turlar = ['мактаб', 'поликлиника', 'МФЙ', 'корхона', 'боғча'];
        $tur = fake()->randomElement($turlar);

        return [
            'hokimlik_id' => $hokimlik?->id,
            'kompleks_id' => null,
            'name_cyr' => fake()->numberBetween(1, 99).'-сон '.$tur,
            'name_lat' => null,
            'inn' => fake()->numerify('#########'),
            'phone' => '+99862'.fake()->numerify('#######'),
            'address' => 'Хоразм вилояти, '.fake()->numberBetween(1, 50).'-уй',
            'created_by' => null,
            'is_active' => true,
        ];
    }
}
