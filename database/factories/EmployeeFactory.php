<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Department;
use App\Models\District;
use App\Models\Employee;
use App\Models\Position;
use App\Models\Region;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Employee>
 */
class EmployeeFactory extends Factory
{
    protected $model = Employee::class;

    public function definition(): array
    {
        $region = Region::first() ?? Region::create([
            'name_cyr' => 'Хоразм вилояти',
            'name_lat' => 'Xorazm viloyati',
            'code' => '1733',
        ]);

        $district = District::first() ?? District::create([
            'region_id' => $region->id,
            'name_cyr' => 'Урганч шаҳри',
            'name_lat' => 'Urganch shahri',
            'code' => '1733-01',
        ]);

        $department = Department::first() ?? Department::create([
            'name_cyr' => 'Тест бўлим',
            'name_lat' => 'Test bo\'lim',
            'code' => 'TST',
        ]);

        $position = Position::first() ?? Position::create([
            'name_cyr' => 'Мутахассис',
            'name_lat' => 'Mutaxassis',
        ]);

        return [
            'uuid' => Str::uuid()->toString(),
            'last_name_cyr' => 'Тестов',
            'first_name_cyr' => 'Тест',
            'middle_name_cyr' => 'Тестович',
            'last_name_lat' => 'Testov',
            'first_name_lat' => 'Test',
            'middle_name_lat' => 'Testovich',
            'current_position' => 'Тест бўлими бош мутахассиси',
            'position_start_date' => '2024-01-15',
            'birth_date' => '1990-05-20',
            'birth_place' => 'Хоразм вилояти, Урганч шаҳри',
            'birth_region_id' => $region->id,
            'birth_district_id' => $district->id,
            'nationality' => 'ўзбек',
            'party_affiliation' => 'йўқ',
            'education_level' => 'олий',
            'education_completion' => '2012 йил, Урганч давлат университети (кундузги)',
            'specialty_by_education' => 'иқтисодчи',
            'academic_degree' => 'йўқ',
            'academic_title' => 'йўқ',
            'foreign_languages' => 'инглиз тили',
            'state_awards' => 'тақдирланмаган',
            'elected_body_member' => 'йўқ',
            'jshshir' => fake()->unique()->numerify('##############'),
            'passport_series' => 'AB',
            'passport_number' => fake()->unique()->numerify('#######'),
            'department_id' => $department->id,
            'position_id' => $position->id,
        ];
    }
}
