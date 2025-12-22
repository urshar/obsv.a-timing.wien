<?php

namespace Database\Seeders;

use App\Models\Nation;
use App\Models\Region;
use Illuminate\Database\Seeder;

class AustriaRegionSeeder extends Seeder
{
    public function run(): void
    {
        $aut = Nation::where('iso2', 'AT')->first();

        if (! $aut) {
            return;
        }

        $rows = [
            [
                'nameEn' => 'Burgenland',
                'nameDe' => 'Burgenland',
                'abbreviation' => 'B',
                'isoSubRegionCode' => 'AT-1',
                'lsvCode' => 'BLSV',
                'bsvCode' => 'BBSV',
            ],
            [
                'nameEn' => 'Carinthia',
                'nameDe' => 'Kärnten',
                'abbreviation' => 'K',
                'isoSubRegionCode' => 'AT-2',
                'lsvCode' => 'KLSV',
                'bsvCode' => 'KBSV',
            ],
            [
                'nameEn' => 'Lower Austria',
                'nameDe' => 'Niederösterreich',
                'abbreviation' => 'NÖ',
                'isoSubRegionCode' => 'AT-3',
                'lsvCode' => 'NLSV',
                'bsvCode' => 'NBSV',
            ],
            [
                'nameEn' => 'Upper Austria',
                'nameDe' => 'Oberösterreich',
                'abbreviation' => 'OÖ',
                'isoSubRegionCode' => 'AT-4',
                'lsvCode' => 'OLSV',
                'bsvCode' => 'OBSV',
            ],
            [
                'nameEn' => 'Salzburg',
                'nameDe' => 'Salzburg',
                'abbreviation' => 'S',
                'isoSubRegionCode' => 'AT-5',
                'lsvCode' => 'SLSV',
                'bsvCode' => 'SBSV',
            ],
            [
                'nameEn' => 'Styria',
                'nameDe' => 'Steiermark',
                'abbreviation' => 'ST',
                'isoSubRegionCode' => 'AT-6',
                'lsvCode' => 'STLSV',
                'bsvCode' => 'STBSV',
            ],
            [
                'nameEn' => 'Tyrol',
                'nameDe' => 'Tirol',
                'abbreviation' => 'T',
                'isoSubRegionCode' => 'AT-7',
                'lsvCode' => 'TLSV',
                'bsvCode' => 'TBSV',
            ],
            [
                'nameEn' => 'Vorarlberg',
                'nameDe' => 'Vorarlberg',
                'abbreviation' => 'V',
                'isoSubRegionCode' => 'AT-8',
                'lsvCode' => 'VLSV',
                'bsvCode' => 'VBSV',
            ],
            [
                'nameEn' => 'Vienna',
                'nameDe' => 'Wien',
                'abbreviation' => 'W',
                'isoSubRegionCode' => 'AT-9',
                'lsvCode' => 'WLSV',
                'bsvCode' => 'WBSV',
            ],
        ];

        foreach ($rows as $r) {
            Region::updateOrCreate(
                [
                    'nation_id' => $aut->id,
                    'isoSubRegionCode' => $r['isoSubRegionCode'],
                ],
                array_merge($r, [
                    'nation_id' => $aut->id,
                ])
            );
        }
    }
}
