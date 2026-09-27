<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Position;
use Illuminate\Database\Seeder;

class PositionSeeder extends Seeder
{
    public function run(): void
    {
        $positions = [
            ['name_cyr' => 'Ҳоким', 'name_lat' => 'Hokim', 'role_name' => 'super-admin'],
            ['name_cyr' => 'Ҳоким маслаҳатчиси', 'name_lat' => 'Hokim maslahatchisi', 'role_name' => 'hokim-maslahatchisi'],
            ['name_cyr' => 'Ҳоким ўринбосари', 'name_lat' => 'Hokim orinbosari', 'role_name' => 'hokim-orinbosari'],
            ['name_cyr' => 'Котибият мудири', 'name_lat' => 'Kotibyat mudiri', 'role_name' => 'kotibyat-mudiri'],
            ['name_cyr' => 'Туман ҳокими ўринбосари', 'name_lat' => 'Tuman hokimi orinbosari', 'role_name' => 'tuman-admin'],
            ['name_cyr' => 'Туман ҳокимлиги мутахассиси', 'name_lat' => 'Tuman hokimligi mutaxassisi', 'role_name' => 'tuman-mutaxassis'],
            ['name_cyr' => 'Бўлим бошлиғи', 'name_lat' => 'Bo\'lim boshlig\'i', 'role_name' => 'mutaxassis'],
            ['name_cyr' => 'Бош мутахассис', 'name_lat' => 'Bosh mutaxassis', 'role_name' => 'mutaxassis'],
            ['name_cyr' => 'Етакчи мутахассис', 'name_lat' => 'Yetakchi mutaxassis', 'role_name' => 'mutaxassis'],
            ['name_cyr' => 'Мутахассис', 'name_lat' => 'Mutaxassis', 'role_name' => 'mutaxassis'],
            ['name_cyr' => 'Ахборот таҳлил гуруҳи аъзоси', 'name_lat' => 'Axborot tahlil guruhi', 'role_name' => 'axborot-tahlil'],
            ['name_cyr' => 'Кадрлар бўйича инспектор', 'name_lat' => 'Kadrlar inspektori', 'role_name' => 'kadrlar-xodimi'],
            ['name_cyr' => 'Бош бухгалтер', 'name_lat' => 'Bosh buxgalter', 'role_name' => 'mutaxassis'],
            ['name_cyr' => 'Котиб', 'name_lat' => 'Kotib', 'role_name' => 'mutaxassis'],
            ['name_cyr' => 'Тизим маъмури', 'name_lat' => 'Tizim ma\'muri', 'role_name' => 'super-admin'],
        ];

        foreach ($positions as $i => $position) {
            Position::firstOrCreate(['name_cyr' => $position['name_cyr']], [...$position, 'sort_order' => $i + 1]);
        }
    }
}
