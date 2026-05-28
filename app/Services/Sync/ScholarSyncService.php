<?php

namespace App\Services\Sync;

use App\Models\Dosen;
use App\Models\IntegrasiGoogleScholar;
use App\Models\IntegrasiLogSinkron;
use App\Services\Scholar\ScholarService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class ScholarSyncService
{
    public function __construct(
        private readonly ScholarService $scholarService,
    ) {}

    public function syncByDosen(Dosen $dosen, ?string $batchId = null): array
    {
        $log = IntegrasiLogSinkron::create([
            'sumber' => 'google_scholar',
            'jenis' => 'dosen',
            'status' => 'processing',
            'mulai_pada' => now(),
            'detail' => [
                'dosen_id' => $dosen->id,
                'nama' => $dosen->nama ?? $dosen->nama_dosen,
            ],
        ]);

        $totalPulled = 0;
        $errors = [];

        try {
            $data = $this->scholarService->fetchByNamaProdi(
                $dosen->nama ?? $dosen->nama_dosen,
                $dosen->prodi?->nama_prodi ?? '',
            );

            if (!$data) {
                $log->update([
                    'status' => 'completed',
                    'selesai_pada' => now(),
                    'records_pulled' => 0,
                ]);
                return ['total_pulled' => 0, 'dosen_id' => $dosen->id, 'status' => 'no_data'];
            }

            foreach ($data as $item) {
                IntegrasiGoogleScholar::updateOrCreate(
                    [
                        'dosen_id' => $dosen->id,
                        'google_scholar_id' => $item['id'] ?? $item['paperId'] ?? null,
                    ],
                    [
                        'judul' => $item['title'] ?? 'Unknown',
                        'penulis' => isset($item['authors'])
                            ? (is_array($item['authors'])
                                ? collect($item['authors'])->pluck('name')->implode(', ')
                                : $item['authors'])
                            : null,
                        'jurnal' => $item['venue'] ?? $item['journalName'] ?? null,
                        'tahun' => $item['year'],
                        'doi' => $item['externalIds']?->DOI ?? $item['doi'] ?? null,
                        'url' => $item['url'] ?? $item['paperId'] ?? null,
                        'sitasi' => $item['citationCount'] ?? $item['citations'] ?? 0,
                        'is_verified' => false,
                        'sinkron_pada' => now(),
                    ]
                );
                $totalPulled++;
            }

            $log->update([
                'status' => 'completed',
                'selesai_pada' => now(),
                'records_pulled' => $totalPulled,
            ]);

            return [
                'total_pulled' => $totalPulled,
                'dosen_id' => $dosen->id,
                'status' => 'completed',
            ];

        } catch (\Throwable $e) {
            $log->update([
                'status' => 'failed',
                'selesai_pada' => now(),
                'detail' => array_merge($log->detail ?? [], [
                    'error' => $e->getMessage(),
                ]),
            ]);

            Log::error('Scholar sync failed for dosen ' . $dosen->id . ': ' . $e->getMessage());

            return [
                'total_pulled' => 0,
                'dosen_id' => $dosen->id,
                'status' => 'failed',
                'error' => $e->getMessage(),
            ];
        }
    }

    public function syncAll(?string $batchId = null): array
    {
        $dosens = Dosen::whereNotNull('nama')->orWhereNotNull('nama_dosen')->get();
        $results = [];

        foreach ($dosens as $dosen) {
            $results[] = $this->syncByDosen($dosen, $batchId);
        }

        return $results;
    }
}
