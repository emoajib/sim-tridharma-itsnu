<?php

namespace Tests\Feature\Http;

use App\Models\Dosen;
use App\Models\Mahasiswa;
use App\Models\MahasiswaBimbingan;
use App\Models\PeriodeAkademik;
use App\Models\Prodi;
use Illuminate\Foundation\Testing\RefreshDatabase;

class MahasiswaBimbinganControllerTest extends BaseCrudTestCase
{
    use RefreshDatabase;

    

    protected function routePrefix(): string
    {
        return 'bimbingan';
    }

    protected function modelClass(): string
    {
        return MahasiswaBimbingan::class;
    }

    protected function defaultStoreData(): array
    {
        return [
            'dosen_id' => Dosen::factory()->create()->id,
            'prodi_id' => Prodi::factory()->create()->id,
            'periode_id' => PeriodeAkademik::factory()->create()->id,
            'mahasiswa_id' => Mahasiswa::create([
                'nim' => '123456789',
                'nama' => 'Mahasiswa Test',
                'prodi_id' => Prodi::factory()->create()->id,
                'angkatan' => '2025',
                'status' => 'aktif',
            ])->id,
            'jenis_bimbingan' => 'Skripsi',
        ];
    }

    protected function defaultUpdateData(): array
    {
        return [
            'dosen_id' => Dosen::factory()->create()->id,
            'prodi_id' => Prodi::factory()->create()->id,
            'periode_id' => PeriodeAkademik::factory()->create()->id,
            'mahasiswa_id' => Mahasiswa::create([
                'nim' => '987654321',
                'nama' => 'Mahasiswa Update',
                'prodi_id' => Prodi::factory()->create()->id,
                'angkatan' => '2025',
                'status' => 'aktif',
            ])->id,
            'jenis_bimbingan' => 'Skripsi',
        ];
    }

    protected function createRecord(): MahasiswaBimbingan
    {
        return MahasiswaBimbingan::create([
            'dosen_id' => Dosen::factory()->create()->id,
            'prodi_id' => Prodi::factory()->create()->id,
            'periode_id' => PeriodeAkademik::factory()->create()->id,
            'mahasiswa_id' => Mahasiswa::create([
                'nim' => '555555555',
                'nama' => 'Mahasiswa Lama',
                'prodi_id' => Prodi::factory()->create()->id,
                'angkatan' => '2024',
                'status' => 'aktif',
            ])->id,
            'jenis_bimbingan' => 'TA',
        ]);
    }
}
