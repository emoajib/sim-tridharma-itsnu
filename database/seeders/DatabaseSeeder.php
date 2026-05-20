<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            RolePermissionSeeder::class,
            DataAkademikSeeder::class,
            AccreditationSeeder::class,
            DosenSeeder::class,
            SettingsSeeder::class,
            KnowledgeBaseCategorySeeder::class,
            AiptSeeder::class,
            DocumentSeeder::class,
            MasterDataSeeder::class,
            AlumniSeeder::class,
            MahasiswaSeeder::class,
            AkreditasiSeeder::class,
            AgentHistorySeeder::class,
            TransaksiPortofolioSeeder::class,
        ]);
    }
}
