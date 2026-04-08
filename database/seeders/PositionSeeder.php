<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Position;
use Illuminate\Database\Seeder;

class PositionSeeder extends Seeder
{
    public function run(): void
    {
        // Умумий лавозимлар (бўлимга боғлиқ эмас)
        $positions = [
            ['name_cyr' => 'Ҳоким', 'name_lat' => 'Hokim'],
            ['name_cyr' => 'Ҳоким ўринбосари', 'name_lat' => 'Hokim o\'rinbosari'],
            ['name_cyr' => 'Бўлим бошлиғи', 'name_lat' => 'Bo\'lim boshlig\'i'],
            ['name_cyr' => 'Бўлим бошлиғи ўринбосари', 'name_lat' => 'Bo\'lim boshlig\'i o\'rinbosari'],
            ['name_cyr' => 'Бош мутахассис', 'name_lat' => 'Bosh mutaxassis'],
            ['name_cyr' => 'Етакчи мутахассис', 'name_lat' => 'Yetakchi mutaxassis'],
            ['name_cyr' => 'Мутахассис', 'name_lat' => 'Mutaxassis'],
            ['name_cyr' => 'Бош бухгалтер', 'name_lat' => 'Bosh buxgalter'],
            ['name_cyr' => 'Бухгалтер', 'name_lat' => 'Buxgalter'],
            ['name_cyr' => 'Маслаҳатчи', 'name_lat' => 'Maslahatchi'],
            ['name_cyr' => 'Котиб', 'name_lat' => 'Kotib'],
            ['name_cyr' => 'Ҳайдовчи', 'name_lat' => 'Haydovchi'],
            ['name_cyr' => 'Тизим маъмури', 'name_lat' => 'Tizim ma\'muri'],
            ['name_cyr' => 'Кадрлар бўйича инспектор', 'name_lat' => 'Kadrlar bo\'yicha inspektor'],
        ];

        foreach ($positions as $i => $position) {
            Position::create([...$position, 'sort_order' => $i + 1]);
        }
    }
}
