<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Dependency order:
     * 1. RolePermissionSeeder     — creates users/roles/permissions (no deps)
     * 2. DataAkademikSeeder       — creates prodi/fakultas/periode (no deps)
     * 3. AccreditationSeeder      — creates lembaga/instrumen/indikator (no deps)
     * 4. DosenSeeder              — needs prodi (2) & users (1)
     * 5. SettingsSeeder            — no deps
     * 6. KnowledgeBaseCategorySeeder — no deps
     * 7. AiptSeeder               — needs periode (2)
     * 8. DocumentSeeder           — needs users (1)
     * 9. MasterDataSeeder         — needs prodi (2) & periode (2)
     * 10. AlumniSeeder             — needs prodi (2) & users (1)
     * 11. MahasiswaSeeder          — needs prodi (2) & users (1)
     * 12. AkreditasiSeeder         — needs prodi (2), periode (2), indikator (3)
     * 13. AgentHistorySeeder       — needs users (1) & prodi (2)
     * 14. TransaksiPortofolioSeeder — needs dosen (4), prodi (2), mk (9), periode (2), mahasiswa (11)
     */
    public function run(): void
    {
        $this->call([
            RolePermissionSeeder::class,
            CompleteUserSeeder::class,
            DataAkademikSeeder::class,
            AccreditationSeeder::class,
            IndikatorAkreditasiSeeder::class,
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
            // \Database\Seeders\StressTestSeeder::class,   // Comment out - data already exists
            IndikatorIkuSeeder::class,
            RkatDemoSeeder::class,
        ]);
    }
}
