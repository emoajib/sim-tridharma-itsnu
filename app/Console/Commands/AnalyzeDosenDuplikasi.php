<?php

namespace App\Console\Commands;

use App\Models\Dosen;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class AnalyzeDosenDuplikasi extends Command
{
    protected $signature = 'sis:analyze-duplikasi {--export=json : Export format (json|csv|both)}';

    protected $description = 'Analyze dosen with multiple user links (READ-ONLY, Mode Aman)';

    public function handle()
    {
        $this->info('🔍 Analyzing dosen with multiple user links (READ-ONLY Mode)...');

        // Get dosen with multiple users using subquery
        $subQuery = DB::table('users')
            ->select('dosen_id', DB::raw('count(*) as user_count'))
            ->whereNotNull('dosen_id')
            ->groupBy('dosen_id')
            ->havingRaw('count(*) > 1');

        $dosenIds = DB::table(DB::raw("({$subQuery->toSql()}) as user_counts"))
            ->mergeBindings($subQuery)
            ->pluck('dosen_id');

        $dosenWithMultipleUsers = Dosen::with(['users.roles', 'prodi.fakultas'])
            ->whereIn('id', $dosenIds)
            ->get()
            ->sortByDesc(fn ($d) => $d->users->count());

        $this->info("📊 Found {$dosenWithMultipleUsers->count()} dosen with multiple users");

        $results = [];
        $stats = [
            'total_dosen_multiple' => 0,
            'by_user_count' => [],
            'by_email_domain' => ['old' => 0, 'new' => 0, 'mixed' => 0],
            'by_suffix' => [],
        ];

        foreach ($dosenWithMultipleUsers as $dosen) {
            $stats['total_dosen_multiple']++;
            $userCount = $dosen->users->count();
            $stats['by_user_count'][$userCount] = ($stats['by_user_count'][$userCount] ?? 0) + 1;

            $usersData = [];
            $domains = [];
            $suffixes = [];

            /** @var \App\Models\User $user */
            foreach ($dosen->users as $user) {
                $email = $user->email;
                $domain = str_contains($email, '@itsnupekalongan.ac.id') ? 'new' : 'old';
                $domains[] = $domain;

                // Check for suffix pattern (.1, .2, .3)
                if (preg_match('/\.(\d+)@/', $email, $matches)) {
                    $suffix = $matches[1];
                    $suffixes[$suffix] = true;
                }

                $usersData[] = [
                    'id' => $user->id,
                    'email' => $email,
                    'name' => $user->name,
                    'roles' => $user->roles->pluck('name')->toArray(),
                    'is_active' => $user->is_active,
                    'created_at' => $user->created_at?->toIso8601String(),
                    'last_login' => null, // Would need login tracking table
                ];
            }

            // Domain analysis
            if (count(array_unique($domains)) > 1) {
                $stats['by_email_domain']['mixed']++;
            } elseif (in_array('old', $domains)) {
                $stats['by_email_domain']['old']++;
            } else {
                $stats['by_email_domain']['new']++;
            }

            // Suffix analysis
            foreach (array_keys($suffixes) as $s) {
                $stats['by_suffix'][$s] = ($stats['by_suffix'][$s] ?? 0) + 1;
            }

            $results[] = [
                'dosen_id' => $dosen->id,
                'dosen_nama' => $dosen->nama,
                'nidn' => $dosen->nidn,
                'prodi' => $dosen->prodi->nama ?? 'N/A',
                'fakultas' => $dosen->prodi->fakultas->nama ?? 'N/A',
                'user_count' => $userCount,
                'users' => $usersData,
            ];
        }

        // Generate report
        $report = [
            'generated_at' => now()->toIso8601String(),
            'statistics' => $stats,
            'dosen_list' => $results,
        ];

        // Save to storage
        $outputDir = 'analysis/sister-duplikasi-2026-05-25';
        Storage::disk('local')->makeDirectory($outputDir);

        if (in_array($this->option('export'), ['json', 'both'])) {
            Storage::disk('local')->put("{$outputDir}/laporan-A1.json", json_encode($report, JSON_PRETTY_PRINT));
            $this->info("💾 JSON report saved to storage/app/{$outputDir}/laporan-A1.json");
        }

        if (in_array($this->option('export'), ['csv', 'both'])) {
            $csv = $this->generateCsv($results);
            Storage::disk('local')->put("{$outputDir}/laporan-A1.csv", $csv);
            $this->info("💾 CSV report saved to storage/app/{$outputDir}/laporan-A1.csv");
        }

        // Print summary table
        $this->newLine();
        $this->info('📈 Summary Statistics:');
        $this->table(['Metric', 'Value'], [
            ['Total Dosen with Multiple Users', $stats['total_dosen_multiple']],
            ['With Old Domain Only', $stats['by_email_domain']['old']],
            ['With New Domain Only', $stats['by_email_domain']['new']],
            ['With Mixed Domains', $stats['by_email_domain']['mixed']],
        ]);

        $this->newLine();
        $this->info('📋 Top 15 Dosen with Most Users:');
        $top15 = array_slice($results, 0, 15);
        $tableData = array_map(function ($d) {
            $emails = implode(', ', array_column($d['users'], 'email'));
            $roles = implode(', ', array_unique(array_merge(...array_column($d['users'], 'roles'))));

            return [
                $d['dosen_nama'],
                $d['nidn'],
                $d['prodi'],
                $d['user_count'],
                $emails,
                $roles,
            ];
        }, $top15);

        $this->table(['Nama Dosen', 'NIDN', 'Prodi', 'User Count', 'Emails', 'Roles'], $tableData);

        $this->newLine();
        $this->info('✅ Analysis complete. Reports saved to storage/app/'.$outputDir);

        return 0;
    }

    private function generateCsv(array $results): string
    {
        $output = fopen('php://temp', 'r+');
        fputcsv($output, ['Dosen ID', 'Nama', 'NIDN', 'Prodi', 'Fakultas', 'User Count', 'Emails', 'Roles']);

        foreach ($results as $d) {
            fputcsv($output, [
                $d['dosen_id'],
                $d['dosen_nama'],
                $d['nidn'],
                $d['prodi'],
                $d['fakultas'],
                $d['user_count'],
                implode('; ', array_column($d['users'], 'email')),
                implode('; ', array_unique(array_merge(...array_column($d['users'], 'roles')))),
            ]);
        }

        rewind($output);
        $csv = stream_get_contents($output);
        fclose($output);

        return $csv;
    }
}
