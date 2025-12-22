<?php

namespace Database\Seeders;

use App\Models\Continent;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class NationFromSqlSeeder extends Seeder
{
    /**
     * @throws FileNotFoundException
     */
    public function run(): void
    {
        DB::table('nations')->truncate();

        $sql = File::get(database_path('seeders/data/nations.sql'));

        $sql = str_replace(["'---'", "'--'"], 'NULL', $sql);

        DB::unprepared($sql);

        // ---------------------------------------------------
        // 2. Kontinente korrekt setzen
        // ---------------------------------------------------

        $continentIds = Continent::pluck('id', 'code');

        DB::table('nations')->update(['continent_id' => null]);

        // Afrika
        DB::table('nations')
            ->whereIn('subRegionName', ['Northern Africa', 'Sub-Saharan Africa'])
            ->update(['continent_id' => $continentIds['AF']]);

        // Asien
        DB::table('nations')
            ->whereIn('subRegionName', [
                'Central Asia',
                'Eastern Asia',
                'South-eastern Asia',
                'Southern Asia',
                'Western Asia',
            ])
            ->update(['continent_id' => $continentIds['AS']]);

        // Europa
        DB::table('nations')
            ->whereIn('subRegionName', [
                'Eastern Europe',
                'Northern Europe',
                'Southern Europe',
                'Western Europe',
            ])
            ->update(['continent_id' => $continentIds['EU']]);

        // Nordamerika (Nordamerika + Karibik + Zentralamerika)
        DB::table('nations')
            ->where('subRegionName', 'Northern America')
            ->update(['continent_id' => $continentIds['NA']]);

        DB::table('nations')
            ->where('subRegionName', 'Latin America and the Caribbean')
            ->whereIn('IntermediateRegionName', ['Caribbean', 'Central America'])
            ->update(['continent_id' => $continentIds['NA']]);

        // Südamerika
        DB::table('nations')
            ->where('subRegionName', 'Latin America and the Caribbean')
            ->where('IntermediateRegionName', 'South America')
            ->update(['continent_id' => $continentIds['SA']]);

        // Ozeanien
        DB::table('nations')
            ->whereIn('subRegionName', [
                'Australia and New Zealand',
                'Melanesia',
                'Micronesia',
                'Polynesia',
            ])
            ->update(['continent_id' => $continentIds['OC']]);

        // Antarktis
        DB::table('nations')
            ->where('nameEn', 'Antarctica')
            ->update(['continent_id' => $continentIds['AN']]);

        // Sonderfall Taipei (hat in den Daten kein subRegionName)
        DB::table('nations')
            ->where('nameEn', 'Taipei')
            ->update(['continent_id' => $continentIds['AS']]);
    }
}
