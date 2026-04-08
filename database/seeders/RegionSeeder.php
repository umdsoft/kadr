<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Region;
use Illuminate\Database\Seeder;

class RegionSeeder extends Seeder
{
    public function run(): void
    {
        $regions = [
            ['name_cyr' => 'Тошкент шаҳри', 'name_lat' => 'Toshkent shahri', 'code' => '1726', 'sort_order' => 1],
            ['name_cyr' => 'Тошкент вилояти', 'name_lat' => 'Toshkent viloyati', 'code' => '1727', 'sort_order' => 2],
            ['name_cyr' => 'Андижон вилояти', 'name_lat' => 'Andijon viloyati', 'code' => '1703', 'sort_order' => 3],
            ['name_cyr' => 'Бухоро вилояти', 'name_lat' => 'Buxoro viloyati', 'code' => '1706', 'sort_order' => 4],
            ['name_cyr' => 'Жиззах вилояти', 'name_lat' => 'Jizzax viloyati', 'code' => '1708', 'sort_order' => 5],
            ['name_cyr' => 'Қашқадарё вилояти', 'name_lat' => 'Qashqadaryo viloyati', 'code' => '1710', 'sort_order' => 6],
            ['name_cyr' => 'Навоий вилояти', 'name_lat' => 'Navoiy viloyati', 'code' => '1712', 'sort_order' => 7],
            ['name_cyr' => 'Наманган вилояти', 'name_lat' => 'Namangan viloyati', 'code' => '1714', 'sort_order' => 8],
            ['name_cyr' => 'Самарқанд вилояти', 'name_lat' => 'Samarqand viloyati', 'code' => '1718', 'sort_order' => 9],
            ['name_cyr' => 'Сурхондарё вилояти', 'name_lat' => 'Surxondaryo viloyati', 'code' => '1722', 'sort_order' => 10],
            ['name_cyr' => 'Сирдарё вилояти', 'name_lat' => 'Sirdaryo viloyati', 'code' => '1724', 'sort_order' => 11],
            ['name_cyr' => 'Фарғона вилояти', 'name_lat' => 'Farg\'ona viloyati', 'code' => '1730', 'sort_order' => 12],
            ['name_cyr' => 'Хоразм вилояти', 'name_lat' => 'Xorazm viloyati', 'code' => '1733', 'sort_order' => 13],
            ['name_cyr' => 'Қорақалпоғистон Республикаси', 'name_lat' => 'Qoraqalpog\'iston Respublikasi', 'code' => '1735', 'sort_order' => 14],
        ];

        foreach ($regions as $region) {
            Region::create($region);
        }
    }
}
