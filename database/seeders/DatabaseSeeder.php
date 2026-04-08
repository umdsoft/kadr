<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $this->call([
            RegionSeeder::class,
            DistrictSeeder::class,
            // MahallaSeeder::class, // Phase 1 da to'ldiriladi
            NationalitySeeder::class,
            SpecialtySeeder::class,
            DepartmentSeeder::class,
            PositionSeeder::class,
            RoleAndPermissionSeeder::class,
            AdminUserSeeder::class,
        ]);
    }
}
