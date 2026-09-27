<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Specialty;
use Illuminate\Database\Seeder;

class SpecialtySeeder extends Seeder
{
    public function run(): void
    {
        $specialties = [
            ['name_cyr' => 'иқтисодчи', 'name_lat' => 'iqtisodchi'],
            ['name_cyr' => 'ҳуқуқшунос', 'name_lat' => 'huquqshunos'],
            ['name_cyr' => 'муҳандис', 'name_lat' => 'muhandis'],
            ['name_cyr' => 'педагог', 'name_lat' => 'pedagog'],
            ['name_cyr' => 'табиб', 'name_lat' => 'tabib'],
            ['name_cyr' => 'қурилишчи', 'name_lat' => 'qurilishchi'],
            ['name_cyr' => 'агроном', 'name_lat' => 'agronom'],
            ['name_cyr' => 'бухгалтер', 'name_lat' => 'buxgalter'],
            ['name_cyr' => 'дастурчи', 'name_lat' => 'dasturchi'],
            ['name_cyr' => 'архитектор', 'name_lat' => 'arxitektor'],
            ['name_cyr' => 'журналист', 'name_lat' => 'jurnalist'],
            ['name_cyr' => 'филолог', 'name_lat' => 'filolog'],
            ['name_cyr' => 'тарихчи', 'name_lat' => 'tarixchi'],
            ['name_cyr' => 'кимёгар', 'name_lat' => 'kimyogar'],
            ['name_cyr' => 'физик', 'name_lat' => 'fizik'],
            ['name_cyr' => 'математик', 'name_lat' => 'matematik'],
            ['name_cyr' => 'биолог', 'name_lat' => 'biolog'],
            ['name_cyr' => 'менежер', 'name_lat' => 'menejer'],
            ['name_cyr' => 'молиячи', 'name_lat' => 'moliyachi'],
        ];

        foreach ($specialties as $i => $specialty) {
            Specialty::firstOrCreate(['name_cyr' => $specialty['name_cyr']], [...$specialty, 'sort_order' => $i + 1]);
        }
    }
}
