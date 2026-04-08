<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Department;
use Illuminate\Database\Seeder;

class DepartmentSeeder extends Seeder
{
    public function run(): void
    {
        // Xorazm viloyat hokimligi tuzilmasi
        $hokimlik = Department::create([
            'name_cyr' => 'Хоразм вилояти ҳокимлиги',
            'name_lat' => 'Xorazm viloyati hokimligi',
            'code' => 'XVHOK',
            'sort_order' => 1,
        ]);

        $subDepartments = [
            ['name_cyr' => 'Кадрлар бўлими', 'name_lat' => 'Kadrlar bo\'limi', 'code' => 'KADR', 'sort_order' => 1],
            ['name_cyr' => 'Молия бўлими', 'name_lat' => 'Moliya bo\'limi', 'code' => 'MOL', 'sort_order' => 2],
            ['name_cyr' => 'Ҳуқуқий таъминот бўлими', 'name_lat' => 'Huquqiy ta\'minot bo\'limi', 'code' => 'HUQ', 'sort_order' => 3],
            ['name_cyr' => 'Иқтисодиёт бўлими', 'name_lat' => 'Iqtisodiyot bo\'limi', 'code' => 'IQT', 'sort_order' => 4],
            ['name_cyr' => 'Қурилиш ва инфратузилма бўлими', 'name_lat' => 'Qurilish va infratuzilma bo\'limi', 'code' => 'QUR', 'sort_order' => 5],
            ['name_cyr' => 'Қишлоқ хўжалиги бўлими', 'name_lat' => 'Qishloq xo\'jaligi bo\'limi', 'code' => 'QXB', 'sort_order' => 6],
            ['name_cyr' => 'Маданият ва туризм бўлими', 'name_lat' => 'Madaniyat va turizm bo\'limi', 'code' => 'MAD', 'sort_order' => 7],
            ['name_cyr' => 'Ижтимоий ривожлантириш бўлими', 'name_lat' => 'Ijtimoiy rivojlantirish bo\'limi', 'code' => 'IJT', 'sort_order' => 8],
            ['name_cyr' => 'Ахборот хизмати', 'name_lat' => 'Axborot xizmati', 'code' => 'AXB', 'sort_order' => 9],
            ['name_cyr' => 'Девонхона', 'name_lat' => 'Devonxona', 'code' => 'DEV', 'sort_order' => 10],
        ];

        foreach ($subDepartments as $dept) {
            Department::create([...$dept, 'parent_id' => $hokimlik->id]);
        }
    }
}
