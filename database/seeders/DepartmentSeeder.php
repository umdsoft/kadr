<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Department;
use Illuminate\Database\Seeder;

/**
 * 3 darajali struktura:
 *   Ҳокимлик (viloyat/shahar/tuman)
 *     └── Комплекс (type='kompleks', kotibyat mudiri boshqaradi)
 *           └── Бошқарма/Бўлим
 */
class DepartmentSeeder extends Seeder
{
    public function run(): void
    {
        // Ҳар ҳокимликда бир хил комплекслар ва уларга тегишли бошқармалар
        $komplekslar = [
            [
                'code' => 'K_IQT',
                'name_cyr' => 'Иқтисодиёт ва тадбиркорлик комплекси',
                'name_lat' => 'Iqtisodiyot va tadbirkorlik kompleksi',
                'boshqarmalar' => [
                    ['code' => 'IQT', 'name_cyr' => 'Иқтисодиёт ва молия бошқармаси', 'name_lat' => 'Iqtisodiyot va moliya boshqarmasi'],
                    ['code' => 'KAM', 'name_cyr' => 'Камбағалликни қисқартириш ва бандлик бошқармаси', 'name_lat' => 'Kambag\'allikni qisqartirish boshqarmasi'],
                ],
            ],
            [
                'code' => 'K_QUR',
                'name_cyr' => 'Қурилиш ва коммунал хўжалик комплекси',
                'name_lat' => 'Qurilish va kommunal xo\'jalik kompleksi',
                'boshqarmalar' => [
                    ['code' => 'QUR', 'name_cyr' => 'Қурилиш ва инфратузилма бошқармаси', 'name_lat' => 'Qurilish va infratuzilma boshqarmasi'],
                ],
            ],
            [
                'code' => 'K_QX',
                'name_cyr' => 'Қишлоқ ва сув хўжалиги комплекси',
                'name_lat' => 'Qishloq va suv xo\'jaligi kompleksi',
                'boshqarmalar' => [
                    ['code' => 'QXB', 'name_cyr' => 'Қишлоқ ва сув хўжалиги бошқармаси', 'name_lat' => 'Qishloq va suv xo\'jaligi boshqarmasi'],
                ],
            ],
            [
                'code' => 'K_IJT',
                'name_cyr' => 'Ижтимоий соҳа комплекси',
                'name_lat' => 'Ijtimoiy soha kompleksi',
                'boshqarmalar' => [
                    ['code' => 'IJT', 'name_cyr' => 'Ижтимоий ривожлантириш бошқармаси', 'name_lat' => 'Ijtimoiy rivojlantirish boshqarmasi'],
                ],
            ],
            [
                'code' => 'K_YOSH',
                'name_cyr' => 'Ёшлар, маънавият ва маърифат комплекси',
                'name_lat' => 'Yoshlar, ma\'naviyat va ma\'rifat kompleksi',
                'boshqarmalar' => [
                    ['code' => 'MAD', 'name_cyr' => 'Маданият ва туризм бошқармаси', 'name_lat' => 'Madaniyat va turizm boshqarmasi'],
                ],
            ],
            [
                'code' => 'K_KOT',
                'name_cyr' => 'Котибият (умумий бошқарув) комплекси',
                'name_lat' => 'Kotibiyat (umumiy boshqaruv) kompleksi',
                'boshqarmalar' => [
                    ['code' => 'KADR', 'name_cyr' => 'Кадрлар бўлими', 'name_lat' => 'Kadrlar bo\'limi'],
                    ['code' => 'HUQ', 'name_cyr' => 'Ҳуқуқий таъминот бўлими', 'name_lat' => 'Huquqiy ta\'minot bo\'limi'],
                    ['code' => 'AXB', 'name_cyr' => 'Ахборот хизмати', 'name_lat' => 'Axborot xizmati'],
                    ['code' => 'DEV', 'name_cyr' => 'Девонхона', 'name_lat' => 'Devonxona'],
                ],
            ],
        ];

        $hokimliklar = [
            ['name_cyr' => 'Хоразм вилояти ҳокимлиги', 'name_lat' => 'Xorazm viloyati hokimligi', 'code' => 'XVHOK', 'type' => 'viloyat', 'sort_order' => 1],
            ['name_cyr' => 'Урганч шаҳар ҳокимлиги', 'name_lat' => 'Urganch shahar hokimligi', 'code' => 'URG', 'type' => 'shahar', 'sort_order' => 2],
            ['name_cyr' => 'Хива шаҳар ҳокимлиги', 'name_lat' => 'Xiva shahar hokimligi', 'code' => 'XIV', 'type' => 'shahar', 'sort_order' => 3],
            ['name_cyr' => 'Питнак шаҳар ҳокимлиги', 'name_lat' => 'Pitnak shahar hokimligi', 'code' => 'PIT', 'type' => 'shahar', 'sort_order' => 4],
            ['name_cyr' => 'Боғот туман ҳокимлиги', 'name_lat' => 'Bog\'ot tuman hokimligi', 'code' => 'BOG', 'type' => 'tuman', 'sort_order' => 5],
            ['name_cyr' => 'Гурлан туман ҳокимлиги', 'name_lat' => 'Gurlan tuman hokimligi', 'code' => 'GUR', 'type' => 'tuman', 'sort_order' => 6],
            ['name_cyr' => 'Қўшкўпир туман ҳокимлиги', 'name_lat' => 'Qo\'shko\'pir tuman hokimligi', 'code' => 'QKP', 'type' => 'tuman', 'sort_order' => 7],
            ['name_cyr' => 'Тупроққалъа туман ҳокимлиги', 'name_lat' => 'Tuproqqal\'a tuman hokimligi', 'code' => 'TQA', 'type' => 'tuman', 'sort_order' => 8],
            ['name_cyr' => 'Урганч туман ҳокимлиги', 'name_lat' => 'Urganch tuman hokimligi', 'code' => 'URT', 'type' => 'tuman', 'sort_order' => 9],
            ['name_cyr' => 'Хазорасп туман ҳокимлиги', 'name_lat' => 'Xazorasp tuman hokimligi', 'code' => 'XAZ', 'type' => 'tuman', 'sort_order' => 10],
            ['name_cyr' => 'Хонқа туман ҳокимлиги', 'name_lat' => 'Xonqa tuman hokimligi', 'code' => 'XON', 'type' => 'tuman', 'sort_order' => 11],
            ['name_cyr' => 'Шовот туман ҳокимлиги', 'name_lat' => 'Shovot tuman hokimligi', 'code' => 'SHV', 'type' => 'tuman', 'sort_order' => 12],
            ['name_cyr' => 'Янгиариқ туман ҳокимлиги', 'name_lat' => 'Yangiariq tuman hokimligi', 'code' => 'YAN', 'type' => 'tuman', 'sort_order' => 13],
            ['name_cyr' => 'Янгибозор туман ҳокимлиги', 'name_lat' => 'Yangibozor tuman hokimligi', 'code' => 'YNB', 'type' => 'tuman', 'sort_order' => 14],
        ];

        foreach ($hokimliklar as $hok) {
            $hokimlik = Department::firstOrCreate(['code' => $hok['code']], $hok);

            foreach ($komplekslar as $ki => $kompleks) {
                $k = Department::firstOrCreate(['code' => $hokimlik->code.'_'.$kompleks['code']], [
                    'name_cyr' => $kompleks['name_cyr'],
                    'name_lat' => $kompleks['name_lat'],
                    'type' => 'kompleks',
                    'parent_id' => $hokimlik->id,
                    'sort_order' => $ki + 1,
                ]);

                foreach ($kompleks['boshqarmalar'] as $bi => $bosh) {
                    Department::firstOrCreate(['code' => $hokimlik->code.'_'.$bosh['code']], [
                        'name_cyr' => $bosh['name_cyr'],
                        'name_lat' => $bosh['name_lat'],
                        'parent_id' => $k->id,
                        'sort_order' => $bi + 1,
                    ]);
                }
            }
        }
    }
}
