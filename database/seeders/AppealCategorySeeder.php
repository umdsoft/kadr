<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\AppealCategory;
use Illuminate\Database\Seeder;

class AppealCategorySeeder extends Seeder
{
    public function run(): void
    {
        // 6 ta asosiy domen — har biri ichida sub-kategoriyalar
        $tree = [
            [
                'code' => 'kredit',
                'name_cyr' => 'Кредит',
                'name_lat' => 'Kredit',
                'icon' => 'currency-dollar',
                'default_route_type' => 'council',
                'default_sla_hours' => 168, // 7 kun
                'children' => [
                    ['code' => 'kredit.mikro', 'name_cyr' => 'Микрокредит', 'default_sla_hours' => 168],
                    ['code' => 'kredit.ipoteka', 'name_cyr' => 'Ипотека', 'default_sla_hours' => 720], // 30 kun
                    ['code' => 'kredit.biznes', 'name_cyr' => 'Бизнес-кредит', 'default_sla_hours' => 240],
                    ['code' => 'kredit.sotsial', 'name_cyr' => 'Ижтимоий кредит', 'default_sla_hours' => 168],
                ],
            ],
            [
                'code' => 'yer',
                'name_cyr' => 'Ер ажратиш',
                'name_lat' => 'Yer ajratish',
                'icon' => 'map',
                'default_route_type' => 'department',
                'default_sla_hours' => 720,
                'children' => [
                    ['code' => 'yer.tomorqa', 'name_cyr' => 'Томорқа', 'default_sla_hours' => 240],
                    ['code' => 'yer.dehqonchilik', 'name_cyr' => 'Деҳқончилик', 'default_sla_hours' => 720],
                    ['code' => 'yer.ishlab_chiqarish', 'name_cyr' => 'Ишлаб чиқариш', 'default_sla_hours' => 720],
                    ['code' => 'yer.tijorat', 'name_cyr' => 'Тижорат', 'default_sla_hours' => 720],
                ],
            ],
            [
                'code' => 'ish',
                'name_cyr' => 'Иш ўрни',
                'name_lat' => 'Ish o\'rni',
                'icon' => 'briefcase',
                'default_route_type' => 'council',
                'default_sla_hours' => 168,
                'children' => [
                    ['code' => 'ish.kasb_oqitish', 'name_cyr' => 'Касбга ўқитиш'],
                    ['code' => 'ish.ish_topish', 'name_cyr' => 'Иш топиш'],
                    ['code' => 'ish.korxona', 'name_cyr' => 'Корхона очиш'],
                ],
            ],
            [
                'code' => 'talim',
                'name_cyr' => 'Таълим',
                'name_lat' => 'Talim',
                'icon' => 'academic-cap',
                'default_route_type' => 'council',
                'default_sla_hours' => 240,
                'children' => [
                    ['code' => 'talim.universitet', 'name_cyr' => 'Университет'],
                    ['code' => 'talim.granty', 'name_cyr' => 'Грантлар'],
                    ['code' => 'talim.magistratura', 'name_cyr' => 'Магистратура'],
                    ['code' => 'talim.xorij', 'name_cyr' => 'Хориж'],
                ],
            ],
            [
                'code' => 'ijtimoiy',
                'name_cyr' => 'Ижтимоий ёрдам',
                'name_lat' => 'Ijtimoiy yordam',
                'icon' => 'heart',
                'default_route_type' => 'council',
                'default_sla_hours' => 120,
                'children' => [
                    ['code' => 'ijtimoiy.tibbiy', 'name_cyr' => 'Тиббий ёрдам'],
                    ['code' => 'ijtimoiy.nogironlik', 'name_cyr' => 'Ногиронлик'],
                    ['code' => 'ijtimoiy.ona_bola', 'name_cyr' => 'Она-бола'],
                ],
            ],
            [
                'code' => 'huquqiy',
                'name_cyr' => 'Ҳуқуқий масалалар',
                'name_lat' => 'Huquqiy',
                'icon' => 'scale',
                'default_route_type' => 'council',
                'default_sla_hours' => 240,
                'children' => [
                    ['code' => 'huquqiy.hujjat', 'name_cyr' => 'Ҳужжат'],
                    ['code' => 'huquqiy.nikoh', 'name_cyr' => 'Никоҳ'],
                    ['code' => 'huquqiy.mehnat', 'name_cyr' => 'Меҳнат ҳужжати'],
                ],
            ],
        ];

        $sortOrder = 1;
        foreach ($tree as $domain) {
            $children = $domain['children'] ?? [];
            unset($domain['children']);

            $parent = AppealCategory::firstOrCreate(
                ['code' => $domain['code']],
                [...$domain, 'sort_order' => $sortOrder++],
            );

            foreach ($children as $i => $child) {
                AppealCategory::firstOrCreate(
                    ['code' => $child['code']],
                    [
                        'parent_id' => $parent->id,
                        'name_cyr' => $child['name_cyr'],
                        'name_lat' => $child['name_lat'] ?? null,
                        'default_sla_hours' => $child['default_sla_hours'] ?? $domain['default_sla_hours'],
                        'default_route_type' => $domain['default_route_type'],
                        'sort_order' => $i + 1,
                    ],
                );
            }
        }
    }
}
