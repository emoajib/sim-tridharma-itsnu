<?php

namespace Tests\Feature\Services;

use App\Services\Sinta\SintaImportService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Maatwebsite\Excel\Facades\Excel;
use Tests\TestCase;

class SintaImportServiceTest extends TestCase
{
    use RefreshDatabase;

    private SintaImportService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new SintaImportService;
    }

    public function test_universal_import_succeeds_with_xlsx(): void
    {
        $importClass = new class
        {
            public function import($reader) {}
        };

        Excel::shouldReceive('import')
            ->once()
            ->andReturnNull();

        $file = UploadedFile::fake()->create('data.xlsx');

        $this->service->universalImport($importClass, $file);
    }

    public function test_universal_import_tries_all_readers(): void
    {
        $attempts = 0;
        $importClass = new class
        {
            public function import($reader) {}
        };

        Excel::shouldReceive('import')
            ->times(5)
            ->andReturnUsing(function () use (&$attempts) {
                $attempts++;
                if ($attempts < 5) {
                    throw new \Exception('Format not supported');
                }
            });

        $file = UploadedFile::fake()->create('data.csv');

        $this->service->universalImport($importClass, $file);

        $this->assertEquals(5, $attempts);
    }

    public function test_universal_import_throws_when_all_readers_fail(): void
    {
        $importClass = new class
        {
            public function import($reader) {}
        };

        Excel::shouldReceive('import')
            ->times(6)
            ->andThrow(new \Exception('Format not supported'));

        $file = UploadedFile::fake()->create('data.unknown');

        $this->expectException(\Exception::class);
        $this->service->universalImport($importClass, $file);
    }

    public function test_import_penelitian_returns_count(): void
    {
        Excel::shouldReceive('import')
            ->times(2)
            ->andReturnNull();

        $result = $this->service->importPenelitian(
            UploadedFile::fake()->create('penelitian.xlsx')
        );

        $this->assertEquals('penelitian', $result['type']);
        $this->assertEquals(0, $result['imported']);
    }

    public function test_import_publikasi_returns_count(): void
    {
        Excel::shouldReceive('import')
            ->times(2)
            ->andReturnNull();

        $result = $this->service->importPublikasi(
            UploadedFile::fake()->create('publikasi.xlsx')
        );

        $this->assertEquals('publikasi', $result['type']);
        $this->assertEquals(0, $result['imported']);
    }

    public function test_import_pkm_returns_count(): void
    {
        Excel::shouldReceive('import')
            ->times(2)
            ->andReturnNull();

        $result = $this->service->importPkm(
            UploadedFile::fake()->create('pkm.xlsx')
        );

        $this->assertEquals('pkm', $result['type']);
        $this->assertEquals(0, $result['imported']);
    }
}
