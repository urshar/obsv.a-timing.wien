<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        DB::statement('PRAGMA foreign_keys=OFF');

        DB::table('regions')->delete();
        DB::table('nations')->delete();

        DB::table('sqlite_sequence')->where('name', 'nations')->delete();

        DB::statement('PRAGMA foreign_keys=ON');

        User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

        $this->call([
            ContinentSeeder::class,
            NationFromSqlSeeder::class,
            AustriaRegionSeeder::class,
        ]);
    }
}
