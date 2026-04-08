<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Nationality;
use Illuminate\Database\Seeder;

class NationalitySeeder extends Seeder
{
    public function run(): void
    {
        $nationalities = [
            ['name_cyr' => 'ўзбек', 'name_lat' => 'o\'zbek', 'sort_order' => 1],
            ['name_cyr' => 'қорақалпоқ', 'name_lat' => 'qoraqalpoq', 'sort_order' => 2],
            ['name_cyr' => 'рус', 'name_lat' => 'rus', 'sort_order' => 3],
            ['name_cyr' => 'тожик', 'name_lat' => 'tojik', 'sort_order' => 4],
            ['name_cyr' => 'қозоқ', 'name_lat' => 'qozoq', 'sort_order' => 5],
            ['name_cyr' => 'туркман', 'name_lat' => 'turkman', 'sort_order' => 6],
            ['name_cyr' => 'қирғиз', 'name_lat' => 'qirg\'iz', 'sort_order' => 7],
            ['name_cyr' => 'татар', 'name_lat' => 'tatar', 'sort_order' => 8],
            ['name_cyr' => 'корейс', 'name_lat' => 'koreys', 'sort_order' => 9],
            ['name_cyr' => 'украин', 'name_lat' => 'ukrain', 'sort_order' => 10],
            ['name_cyr' => 'араб', 'name_lat' => 'arab', 'sort_order' => 11],
            ['name_cyr' => 'бошқа', 'name_lat' => 'boshqa', 'sort_order' => 99],
        ];

        foreach ($nationalities as $nationality) {
            Nationality::create($nationality);
        }
    }
}
