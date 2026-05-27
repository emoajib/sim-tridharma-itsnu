<?php

namespace Tests\Feature\Http;

use App\Models\DokumenBukti;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;

class DokumenBuktiControllerTest extends BaseCrudTestCase
{
    use RefreshDatabase;

    

    protected function routePrefix(): string
    {
        return 'dokumen';
    }

    protected function modelClass(): string
    {
        return DokumenBukti::class;
    }

    protected function defaultStoreData(): array
    {
        return [
            'nama_dokumen' => 'Dokumen Test',
            'file' => UploadedFile::fake()->create('dokumen.pdf', 100, 'application/pdf'),
        ];
    }

    protected function defaultUpdateData(): array
    {
        return [
            'nama_dokumen' => 'Dokumen Updated',
        ];
    }

    protected function createRecord(): DokumenBukti
    {
        return DokumenBukti::create([
            'nama_dokumen' => 'Dokumen Lama',
            'file_path' => 'dokumen/test.pdf',
            'file_type' => 'pdf',
            'file_size' => 1024,
            'hash' => md5('test'),
        ]);
    }
}
