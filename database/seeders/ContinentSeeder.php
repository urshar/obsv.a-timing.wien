<?php

namespace Database\Seeders;

use App\Models\Continent;
use Illuminate\Database\Seeder;

class ContinentSeeder extends Seeder
{
    public function run(): void
    {
        $rows = [
            ['code' => 'AF', 'nameEn' => 'Africa', 'nameDe' => 'Afrika'],
            ['code' => 'AN', 'nameEn' => 'Antarctica', 'nameDe' => 'Antarktis'],
            ['code' => 'AS', 'nameEn' => 'Asia', 'nameDe' => 'Asien'],
            ['code' => 'EU', 'nameEn' => 'Europe', 'nameDe' => 'Europa'],
            ['code' => 'NA', 'nameEn' => 'North America', 'nameDe' => 'Nordamerika'],
            ['code' => 'OC', 'nameEn' => 'Oceania', 'nameDe' => 'Ozeanien'],
            ['code' => 'SA', 'nameEn' => 'South America', 'nameDe' => 'Südamerika'],
        ];

        foreach ($rows as $r) {
            Continent::updateOrCreate(['code' => $r['code']], $r);
        }
    }
}
