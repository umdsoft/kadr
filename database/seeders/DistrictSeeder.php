<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\District;
use App\Models\Region;
use Illuminate\Database\Seeder;

class DistrictSeeder extends Seeder
{
    public function run(): void
    {
        // Xorazm viloyati tumanlarini batafsil seed qilamiz
        $xorazm = Region::where('code', '1733')->firstOrFail();

        $xorazmDistricts = [
            ['name_cyr' => 'Урганч шаҳри', 'name_lat' => 'Urganch shahri', 'code' => '1733-01', 'is_city' => true, 'sort_order' => 1],
            ['name_cyr' => 'Хива шаҳри', 'name_lat' => 'Xiva shahri', 'code' => '1733-02', 'is_city' => true, 'sort_order' => 2],
            ['name_cyr' => 'Боғот тумани', 'name_lat' => 'Bog\'ot tumani', 'code' => '1733-03', 'sort_order' => 3],
            ['name_cyr' => 'Гурлан тумани', 'name_lat' => 'Gurlan tumani', 'code' => '1733-04', 'sort_order' => 4],
            ['name_cyr' => 'Қўшкўпир тумани', 'name_lat' => 'Qo\'shko\'pir tumani', 'code' => '1733-05', 'sort_order' => 5],
            ['name_cyr' => 'Урганч тумани', 'name_lat' => 'Urganch tumani', 'code' => '1733-06', 'sort_order' => 6],
            ['name_cyr' => 'Хазорасп тумани', 'name_lat' => 'Xazorasp tumani', 'code' => '1733-07', 'sort_order' => 7],
            ['name_cyr' => 'Хива тумани', 'name_lat' => 'Xiva tumani', 'code' => '1733-08', 'sort_order' => 8],
            ['name_cyr' => 'Хонқа тумани', 'name_lat' => 'Xonqa tumani', 'code' => '1733-09', 'sort_order' => 9],
            ['name_cyr' => 'Шовот тумани', 'name_lat' => 'Shovot tumani', 'code' => '1733-10', 'sort_order' => 10],
            ['name_cyr' => 'Янгиариқ тумани', 'name_lat' => 'Yangiariq tumani', 'code' => '1733-11', 'sort_order' => 11],
            ['name_cyr' => 'Янгибозор тумани', 'name_lat' => 'Yangibozor tumani', 'code' => '1733-12', 'sort_order' => 12],
            ['name_cyr' => 'Тупроққалъа тумани', 'name_lat' => 'Tuproqqal\'a tumani', 'code' => '1733-13', 'sort_order' => 13],
        ];

        foreach ($xorazmDistricts as $district) {
            District::firstOrCreate(['code' => $district['code']], [...$district, 'region_id' => $xorazm->id]);
        }
    }
}
