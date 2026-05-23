<?php

namespace Tests\Feature;

use App\Http\Requests\Admin\FileUploadRequest;
use App\Http\Requests\Admin\SettingsUpdateRequest;
use App\Http\Requests\AgentRunRequest;
use App\Http\Requests\AuditMutuRequest;
use App\Http\Requests\DokumenBuktiRequest;
use App\Http\Requests\DosenRequest;
use App\Http\Requests\FakultasRequest;
use App\Http\Requests\ImportPreviewRequest;
use App\Http\Requests\IndikatorAkreditasiRequest;
use App\Http\Requests\InstrumenAkreditasiRequest;
use App\Http\Requests\KegiatanPendidikanRequest;
use App\Http\Requests\KerjasamaRequest;
use App\Http\Requests\KnowledgeBase\AskRequest;
use App\Http\Requests\KnowledgeBase\UploadRequest;
use App\Http\Requests\LembagaAkreditasiRequest;
use App\Http\Requests\MataKuliahRequest;
use App\Http\Requests\PenelitianRequest;
use App\Http\Requests\PeriodeAkademikRequest;
use App\Http\Requests\ProdiRequest;
use App\Http\Requests\PublikasiRequest;
use App\Http\Requests\RiskRegisterRequest;
use App\Http\Requests\RpsRequest;
use App\Http\Requests\SaranaRequest;
use App\Http\Requests\Sinta\ImportRequest;
use App\Models\Dosen;
use App\Models\Fakultas;
use App\Models\InstrumenAkreditasi;
use App\Models\KnowledgeBaseCategory;
use App\Models\LembagaAkreditasi;
use App\Models\MataKuliah;
use App\Models\Mitra;
use App\Models\PeriodeAkademik;
use App\Models\Prodi;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Routing\Route;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class FormRequestTest extends TestCase
{
    use RefreshDatabase;

    public function test_upload_request_passes_with_valid_data(): void
    {
        $data = [
            'file' => UploadedFile::fake()->create('dokumen.pdf', 100),
            'judul' => 'Dokumen Akreditasi',
            'sumber' => 'BAN-PT',
        ];

        $rules = (new UploadRequest)->rules();
        $validator = Validator::make($data, $rules);

        $this->assertTrue($validator->passes());
    }

    public function test_upload_request_fails_without_file(): void
    {
        $request = new UploadRequest;
        $request->setMethod('POST');
        $data = ['judul' => 'Test'];
        $rules = $request->rules();
        $validator = Validator::make($data, $rules);

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('file', $validator->errors()->toArray());
    }

    public function test_upload_request_fails_without_judul(): void
    {
        $data = [
            'file' => UploadedFile::fake()->create('dokumen.pdf', 100),
        ];
        $rules = (new UploadRequest)->rules();
        $validator = Validator::make($data, $rules);

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('judul', $validator->errors()->toArray());
    }

    public function test_upload_request_fails_with_non_pdf(): void
    {
        $data = [
            'file' => UploadedFile::fake()->create('dokumen.png', 100),
            'judul' => 'Test',
        ];
        $rules = (new UploadRequest)->rules();
        $validator = Validator::make($data, $rules);

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('file', $validator->errors()->toArray());
    }

    public function test_upload_request_fails_with_invalid_category(): void
    {
        $data = [
            'file' => UploadedFile::fake()->create('dokumen.pdf', 100),
            'judul' => 'Test',
            'category_id' => 999,
        ];
        $rules = (new UploadRequest)->rules();
        $validator = Validator::make($data, $rules);

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('category_id', $validator->errors()->toArray());
    }

    public function test_upload_request_passes_with_valid_category(): void
    {
        $category = KnowledgeBaseCategory::create(['nama' => 'Akreditasi']);

        $data = [
            'file' => UploadedFile::fake()->create('dokumen.pdf', 100),
            'judul' => 'Test',
            'category_id' => $category->id,
        ];
        $rules = (new UploadRequest)->rules();
        $validator = Validator::make($data, $rules);

        $this->assertTrue($validator->passes());
    }

    public function test_ask_request_passes_with_valid_data(): void
    {
        $data = ['question' => 'Apa itu akreditasi?'];
        $rules = (new AskRequest)->rules();
        $validator = Validator::make($data, $rules);

        $this->assertTrue($validator->passes());
    }

    public function test_ask_request_fails_without_question(): void
    {
        $data = [];
        $rules = (new AskRequest)->rules();
        $validator = Validator::make($data, $rules);

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('question', $validator->errors()->toArray());
    }

    public function test_ask_request_fails_with_long_question(): void
    {
        $data = ['question' => str_repeat('a', 1001)];
        $rules = (new AskRequest)->rules();
        $validator = Validator::make($data, $rules);

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('question', $validator->errors()->toArray());
    }

    public function test_ask_request_fails_with_invalid_category(): void
    {
        $data = [
            'question' => 'Test',
            'category_id' => 999,
        ];
        $rules = (new AskRequest)->rules();
        $validator = Validator::make($data, $rules);

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('category_id', $validator->errors()->toArray());
    }

    public function test_upload_request_judul_max_255(): void
    {
        $data = [
            'file' => UploadedFile::fake()->create('dokumen.pdf', 100),
            'judul' => str_repeat('a', 256),
        ];
        $rules = (new UploadRequest)->rules();
        $validator = Validator::make($data, $rules);

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('judul', $validator->errors()->toArray());
    }

    public function test_import_request_passes_with_valid_file(): void
    {
        $data = [
            'file' => UploadedFile::fake()->create('data.xlsx', 100),
        ];
        $rules = (new ImportRequest)->rules();
        $validator = Validator::make($data, $rules);

        $this->assertTrue($validator->passes());
    }

    public function test_import_request_fails_without_file(): void
    {
        $data = [];
        $rules = (new ImportRequest)->rules();
        $validator = Validator::make($data, $rules);

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('file', $validator->errors()->toArray());
    }

    public function test_import_request_fails_with_invalid_format(): void
    {
        $data = [
            'file' => UploadedFile::fake()->create('data.pdf', 100),
        ];
        $rules = (new ImportRequest)->rules();
        $validator = Validator::make($data, $rules);

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('file', $validator->errors()->toArray());
    }

    public function test_import_request_fails_with_oversized_file(): void
    {
        $data = [
            'file' => UploadedFile::fake()->create('data.xlsx', 12000),
        ];
        $rules = (new ImportRequest)->rules();
        $validator = Validator::make($data, $rules);

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('file', $validator->errors()->toArray());
    }

    public function test_import_request_accepts_csv(): void
    {
        $data = [
            'file' => UploadedFile::fake()->create('data.csv', 100),
        ];
        $rules = (new ImportRequest)->rules();
        $validator = Validator::make($data, $rules);

        $this->assertTrue($validator->passes());
    }

    public function test_settings_request_passes_with_valid_data(): void
    {
        $data = [
            'settings' => ['theme_mode' => 'gelap', 'theme_color' => 'indigo'],
        ];
        $rules = (new SettingsUpdateRequest)->rules();
        $validator = Validator::make($data, $rules);

        $this->assertTrue($validator->passes());
    }

    public function test_settings_request_fails_without_settings(): void
    {
        $data = [];
        $rules = (new SettingsUpdateRequest)->rules();
        $validator = Validator::make($data, $rules);

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('settings', $validator->errors()->toArray());
    }

    public function test_settings_request_fails_with_non_array(): void
    {
        $data = ['settings' => 'not-an-array'];
        $rules = (new SettingsUpdateRequest)->rules();
        $validator = Validator::make($data, $rules);

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('settings', $validator->errors()->toArray());
    }

    public function test_instrumen_akreditasi_request_passes_with_valid_data(): void
    {
        $lembaga = LembagaAkreditasi::create([
            'nama_lembaga' => 'BAN-PT',
            'singkatan' => 'BAN-PT',
        ]);

        $data = [
            'lembaga_id' => $lembaga->id,
            'nama_instrumen' => 'Instrumen Akreditasi 2025',
        ];
        $rules = (new InstrumenAkreditasiRequest)->rules();
        $validator = Validator::make($data, $rules);

        $this->assertTrue($validator->passes());
    }

    public function test_instrumen_akreditasi_request_fails_without_lembaga_id(): void
    {
        $data = ['nama_instrumen' => 'Test'];
        $rules = (new InstrumenAkreditasiRequest)->rules();
        $validator = Validator::make($data, $rules);

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('lembaga_id', $validator->errors()->toArray());
    }

    public function test_instrumen_akreditasi_request_fails_without_nama_instrumen(): void
    {
        $data = ['lembaga_id' => 1];
        $rules = (new InstrumenAkreditasiRequest)->rules();
        $validator = Validator::make($data, $rules);

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('nama_instrumen', $validator->errors()->toArray());
    }

    public function test_instrumen_akreditasi_request_fails_with_long_nama(): void
    {
        $data = [
            'lembaga_id' => 1,
            'nama_instrumen' => str_repeat('a', 101),
        ];
        $rules = (new InstrumenAkreditasiRequest)->rules();
        $validator = Validator::make($data, $rules);

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('nama_instrumen', $validator->errors()->toArray());
    }

    public function test_instrumen_akreditasi_request_fails_with_invalid_lembaga(): void
    {
        $data = [
            'lembaga_id' => 999,
            'nama_instrumen' => 'Test',
        ];
        $rules = (new InstrumenAkreditasiRequest)->rules();
        $validator = Validator::make($data, $rules);

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('lembaga_id', $validator->errors()->toArray());
    }

    public function test_instrumen_akreditasi_request_passes_with_matriks_kriteria(): void
    {
        $lembaga = LembagaAkreditasi::create([
            'nama_lembaga' => 'BAN-PT',
            'singkatan' => 'BAN-PT',
        ]);

        $data = [
            'lembaga_id' => $lembaga->id,
            'nama_instrumen' => 'Test',
            'matriks_kriteria' => [['kode' => 'A', 'bobot' => 100]],
        ];
        $rules = (new InstrumenAkreditasiRequest)->rules();
        $validator = Validator::make($data, $rules);

        $this->assertTrue($validator->passes());
    }

    public function test_import_preview_request_passes_with_xlsx(): void
    {
        $data = [
            'file' => UploadedFile::fake()->create('instrumen.xlsx', 100),
        ];
        $rules = (new ImportPreviewRequest)->rules();
        $validator = Validator::make($data, $rules);

        $this->assertTrue($validator->passes());
    }

    public function test_import_preview_request_passes_with_csv(): void
    {
        $data = [
            'file' => UploadedFile::fake()->create('instrumen.csv', 100),
        ];
        $rules = (new ImportPreviewRequest)->rules();
        $validator = Validator::make($data, $rules);

        $this->assertTrue($validator->passes());
    }

    public function test_import_preview_request_fails_without_file(): void
    {
        $data = [];
        $rules = (new ImportPreviewRequest)->rules();
        $validator = Validator::make($data, $rules);

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('file', $validator->errors()->toArray());
    }

    public function test_import_preview_request_fails_with_invalid_format(): void
    {
        $data = [
            'file' => UploadedFile::fake()->create('instrumen.pdf', 100),
        ];
        $rules = (new ImportPreviewRequest)->rules();
        $validator = Validator::make($data, $rules);

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('file', $validator->errors()->toArray());
    }

    public function test_dosen_request_passes_with_valid_data(): void
    {
        $prodi = Prodi::create([
            'kode_prodi' => '55201',
            'nama_prodi' => 'Teknik Informatika',
            'fakultas_id' => Fakultas::create(['kode_fakultas' => 'FTIK', 'nama_fakultas' => 'FTIK'])->id,
            'jenjang' => 'S1',
        ]);

        $data = [
            'nidn' => '1234567890',
            'nama_depan' => 'John',
            'nama_belakang' => 'Doe',
            'prodi_id' => $prodi->id,
        ];
        $rules = (new DosenRequest)->rules();
        $validator = Validator::make($data, $rules);

        $this->assertTrue($validator->passes());
    }

    public function test_dosen_request_fails_without_nidn(): void
    {
        $data = ['nama_depan' => 'John'];
        $rules = (new DosenRequest)->rules();
        $validator = Validator::make($data, $rules);

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('nidn', $validator->errors()->toArray());
    }

    public function test_dosen_request_fails_without_nama_depan(): void
    {
        $data = ['nidn' => '1234567890'];
        $rules = (new DosenRequest)->rules();
        $validator = Validator::make($data, $rules);

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('nama_depan', $validator->errors()->toArray());
    }

    public function test_dosen_request_fails_with_duplicate_nidn_on_store(): void
    {
        $prodi = Prodi::create([
            'kode_prodi' => '55201',
            'nama_prodi' => 'Teknik Informatika',
            'fakultas_id' => Fakultas::create(['kode_fakultas' => 'FTIK', 'nama_fakultas' => 'FTIK'])->id,
            'jenjang' => 'S1',
        ]);
        Dosen::create(['nidn' => '1234567890', 'nama_depan' => 'John', 'prodi_id' => $prodi->id]);

        $data = [
            'nidn' => '1234567890',
            'nama_depan' => 'Jane',
            'prodi_id' => $prodi->id,
        ];
        $rules = (new DosenRequest)->rules();
        $validator = Validator::make($data, $rules);

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('nidn', $validator->errors()->toArray());
    }

    public function test_dosen_request_passes_with_same_nidn_on_update(): void
    {
        $prodi = Prodi::create([
            'kode_prodi' => '55201',
            'nama_prodi' => 'Teknik Informatika',
            'fakultas_id' => Fakultas::create(['kode_fakultas' => 'FTIK', 'nama_fakultas' => 'FTIK'])->id,
            'jenjang' => 'S1',
        ]);
        $dosen = Dosen::create(['nidn' => '1234567890', 'nama_depan' => 'John', 'prodi_id' => $prodi->id]);

        $request = new DosenRequest;
        $routeMock = $this->createMock(Route::class);
        $routeMock->method('parameter')->with('dosen')->willReturn($dosen);
        $request->setRouteResolver(fn () => $routeMock);

        $data = [
            'nidn' => '1234567890',
            'nama_depan' => 'John',
            'prodi_id' => $prodi->id,
        ];
        $rules = $request->rules();
        $validator = Validator::make($data, $rules);

        $this->assertTrue($validator->passes());
    }

    public function test_dosen_request_fails_with_invalid_prodi(): void
    {
        $data = [
            'nidn' => '1234567890',
            'nama_depan' => 'John',
            'prodi_id' => 999,
        ];
        $rules = (new DosenRequest)->rules();
        $validator = Validator::make($data, $rules);

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('prodi_id', $validator->errors()->toArray());
    }

    public function test_prodi_request_passes_with_valid_data(): void
    {
        $fakultas = Fakultas::create(['kode_fakultas' => 'FTIK', 'nama_fakultas' => 'FTIK']);

        $data = [
            'kode_prodi' => '55201',
            'nama_prodi' => 'Teknik Informatika',
            'fakultas_id' => $fakultas->id,
            'jenjang' => 'S1',
        ];
        $rules = (new ProdiRequest)->rules();
        $validator = Validator::make($data, $rules);

        $this->assertTrue($validator->passes());
    }

    public function test_prodi_request_fails_without_kode_prodi(): void
    {
        $data = ['nama_prodi' => 'Test', 'jenjang' => 'S1'];
        $rules = (new ProdiRequest)->rules();
        $validator = Validator::make($data, $rules);

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('kode_prodi', $validator->errors()->toArray());
    }

    public function test_prodi_request_fails_without_nama_prodi(): void
    {
        $data = ['kode_prodi' => '55201', 'jenjang' => 'S1'];
        $rules = (new ProdiRequest)->rules();
        $validator = Validator::make($data, $rules);

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('nama_prodi', $validator->errors()->toArray());
    }

    public function test_prodi_request_fails_without_jenjang(): void
    {
        $data = ['kode_prodi' => '55201', 'nama_prodi' => 'Test'];
        $rules = (new ProdiRequest)->rules();
        $validator = Validator::make($data, $rules);

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('jenjang', $validator->errors()->toArray());
    }

    public function test_prodi_request_fails_with_invalid_fakultas(): void
    {
        $data = [
            'kode_prodi' => '55201',
            'nama_prodi' => 'Test',
            'fakultas_id' => 999,
            'jenjang' => 'S1',
        ];
        $rules = (new ProdiRequest)->rules();
        $validator = Validator::make($data, $rules);

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('fakultas_id', $validator->errors()->toArray());
    }

    public function test_prodi_request_passes_with_optional_akreditasi(): void
    {
        $fakultas = Fakultas::create(['kode_fakultas' => 'FTIK', 'nama_fakultas' => 'FTIK']);
        $lembaga = LembagaAkreditasi::create([
            'nama_lembaga' => 'BAN-PT',
            'singkatan' => 'BAN-PT',
        ]);

        $data = [
            'kode_prodi' => '55201',
            'nama_prodi' => 'Teknik Informatika',
            'fakultas_id' => $fakultas->id,
            'jenjang' => 'S1',
            'lembaga_akreditasi_id' => $lembaga->id,
            'akreditasi' => 'Baik',
        ];
        $rules = (new ProdiRequest)->rules();
        $validator = Validator::make($data, $rules);

        $this->assertTrue($validator->passes());
    }

    public function test_kegiatan_pendidikan_request_passes_on_store(): void
    {
        $prodi = Prodi::create([
            'kode_prodi' => '55201',
            'nama_prodi' => 'Teknik Informatika',
            'fakultas_id' => Fakultas::create(['kode_fakultas' => 'FTIK', 'nama_fakultas' => 'FTIK'])->id,
            'jenjang' => 'S1',
        ]);
        $dosen = Dosen::create(['nidn' => '1234567890', 'nama_depan' => 'John', 'prodi_id' => $prodi->id]);
        $periode = PeriodeAkademik::create(['kode_periode' => '20251', 'nama_periode' => '2025/2026 Ganjil']);

        $data = [
            'dosen_id' => $dosen->id,
            'prodi_id' => $prodi->id,
            'periode_id' => $periode->id,
            'nama_kegiatan' => 'Mengajar',
            'jenis_kegiatan' => 'Teori',
            'sks' => 3,
        ];
        $rules = (new KegiatanPendidikanRequest)->rules();
        $validator = Validator::make($data, $rules);

        $this->assertTrue($validator->passes());
    }

    public function test_kegiatan_pendidikan_request_fails_without_sks_on_store(): void
    {
        $prodi = Prodi::create([
            'kode_prodi' => '55201',
            'nama_prodi' => 'Teknik Informatika',
            'fakultas_id' => Fakultas::create(['kode_fakultas' => 'FTIK', 'nama_fakultas' => 'FTIK'])->id,
            'jenjang' => 'S1',
        ]);
        $dosen = Dosen::create(['nidn' => '1234567890', 'nama_depan' => 'John', 'prodi_id' => $prodi->id]);
        $periode = PeriodeAkademik::create(['kode_periode' => '20251', 'nama_periode' => '2025/2026 Ganjil']);

        $data = [
            'dosen_id' => $dosen->id,
            'prodi_id' => $prodi->id,
            'periode_id' => $periode->id,
            'nama_kegiatan' => 'Mengajar',
            'jenis_kegiatan' => 'Teori',
        ];
        $rules = (new KegiatanPendidikanRequest)->rules();
        $validator = Validator::make($data, $rules);

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('sks', $validator->errors()->toArray());
    }

    public function test_kegiatan_pendidikan_request_fails_with_invalid_sks(): void
    {
        $prodi = Prodi::create([
            'kode_prodi' => '55201',
            'nama_prodi' => 'Teknik Informatika',
            'fakultas_id' => Fakultas::create(['kode_fakultas' => 'FTIK', 'nama_fakultas' => 'FTIK'])->id,
            'jenjang' => 'S1',
        ]);
        $dosen = Dosen::create(['nidn' => '1234567890', 'nama_depan' => 'John', 'prodi_id' => $prodi->id]);
        $periode = PeriodeAkademik::create(['kode_periode' => '20251', 'nama_periode' => '2025/2026 Ganjil']);

        $data = [
            'dosen_id' => $dosen->id,
            'prodi_id' => $prodi->id,
            'periode_id' => $periode->id,
            'nama_kegiatan' => 'Mengajar',
            'jenis_kegiatan' => 'Teori',
            'sks' => 99,
        ];
        $rules = (new KegiatanPendidikanRequest)->rules();
        $validator = Validator::make($data, $rules);

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('sks', $validator->errors()->toArray());
    }

    public function test_kegiatan_pendidikan_request_fails_without_dosen(): void
    {
        $data = [
            'nama_kegiatan' => 'Mengajar',
        ];
        $rules = (new KegiatanPendidikanRequest)->rules();
        $validator = Validator::make($data, $rules);

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('dosen_id', $validator->errors()->toArray());
    }

    public function test_penelitian_request_passes_on_store(): void
    {
        $prodi = Prodi::create([
            'kode_prodi' => '55201',
            'nama_prodi' => 'Teknik Informatika',
            'fakultas_id' => Fakultas::create(['kode_fakultas' => 'FTIK', 'nama_fakultas' => 'FTIK'])->id,
            'jenjang' => 'S1',
        ]);
        $dosen = Dosen::create(['nidn' => '1234567890', 'nama_depan' => 'John', 'prodi_id' => $prodi->id]);
        $periode = PeriodeAkademik::create(['kode_periode' => '20251', 'nama_periode' => '2025/2026 Ganjil']);

        $data = [
            'dosen_id' => $dosen->id,
            'prodi_id' => $prodi->id,
            'periode_id' => $periode->id,
            'judul_penelitian' => 'Penelitian AI',
            'jenis_penelitian' => 'Terapan',
            'tahun_pelaksanaan' => '2025',
        ];
        $rules = (new PenelitianRequest)->rules();
        $validator = Validator::make($data, $rules);

        $this->assertTrue($validator->passes());
    }

    public function test_penelitian_request_fails_without_tahun(): void
    {
        $prodi = Prodi::create([
            'kode_prodi' => '55201',
            'nama_prodi' => 'Teknik Informatika',
            'fakultas_id' => Fakultas::create(['kode_fakultas' => 'FTIK', 'nama_fakultas' => 'FTIK'])->id,
            'jenjang' => 'S1',
        ]);
        $dosen = Dosen::create(['nidn' => '1234567890', 'nama_depan' => 'John', 'prodi_id' => $prodi->id]);
        $periode = PeriodeAkademik::create(['kode_periode' => '20251', 'nama_periode' => '2025/2026 Ganjil']);

        $data = [
            'dosen_id' => $dosen->id,
            'prodi_id' => $prodi->id,
            'periode_id' => $periode->id,
            'judul_penelitian' => 'Penelitian AI',
            'jenis_penelitian' => 'Terapan',
        ];
        $rules = (new PenelitianRequest)->rules();
        $validator = Validator::make($data, $rules);

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('tahun_pelaksanaan', $validator->errors()->toArray());
    }

    public function test_penelitian_request_fails_with_invalid_tahun(): void
    {
        $prodi = Prodi::create([
            'kode_prodi' => '55201',
            'nama_prodi' => 'Teknik Informatika',
            'fakultas_id' => Fakultas::create(['kode_fakultas' => 'FTIK', 'nama_fakultas' => 'FTIK'])->id,
            'jenjang' => 'S1',
        ]);
        $dosen = Dosen::create(['nidn' => '1234567890', 'nama_depan' => 'John', 'prodi_id' => $prodi->id]);
        $periode = PeriodeAkademik::create(['kode_periode' => '20251', 'nama_periode' => '2025/2026 Ganjil']);

        $data = [
            'dosen_id' => $dosen->id,
            'prodi_id' => $prodi->id,
            'periode_id' => $periode->id,
            'judul_penelitian' => 'Penelitian AI',
            'jenis_penelitian' => 'Terapan',
            'tahun_pelaksanaan' => '25',
        ];
        $rules = (new PenelitianRequest)->rules();
        $validator = Validator::make($data, $rules);

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('tahun_pelaksanaan', $validator->errors()->toArray());
    }

    public function test_penelitian_request_passes_with_optional_dana(): void
    {
        $prodi = Prodi::create([
            'kode_prodi' => '55201',
            'nama_prodi' => 'Teknik Informatika',
            'fakultas_id' => Fakultas::create(['kode_fakultas' => 'FTIK', 'nama_fakultas' => 'FTIK'])->id,
            'jenjang' => 'S1',
        ]);
        $dosen = Dosen::create(['nidn' => '1234567890', 'nama_depan' => 'John', 'prodi_id' => $prodi->id]);
        $periode = PeriodeAkademik::create(['kode_periode' => '20251', 'nama_periode' => '2025/2026 Ganjil']);

        $data = [
            'dosen_id' => $dosen->id,
            'prodi_id' => $prodi->id,
            'periode_id' => $periode->id,
            'judul_penelitian' => 'Penelitian AI',
            'jenis_penelitian' => 'Terapan',
            'tahun_pelaksanaan' => '2025',
            'sumber_dana' => 'DIKTI',
            'jumlah_dana' => 50000000,
        ];
        $rules = (new PenelitianRequest)->rules();
        $validator = Validator::make($data, $rules);

        $this->assertTrue($validator->passes());
    }

    public function test_penelitian_request_fails_with_negative_dana(): void
    {
        $prodi = Prodi::create([
            'kode_prodi' => '55201',
            'nama_prodi' => 'Teknik Informatika',
            'fakultas_id' => Fakultas::create(['kode_fakultas' => 'FTIK', 'nama_fakultas' => 'FTIK'])->id,
            'jenjang' => 'S1',
        ]);
        $dosen = Dosen::create(['nidn' => '1234567890', 'nama_depan' => 'John', 'prodi_id' => $prodi->id]);
        $periode = PeriodeAkademik::create(['kode_periode' => '20251', 'nama_periode' => '2025/2026 Ganjil']);

        $data = [
            'dosen_id' => $dosen->id,
            'prodi_id' => $prodi->id,
            'periode_id' => $periode->id,
            'judul_penelitian' => 'Penelitian AI',
            'jenis_penelitian' => 'Terapan',
            'tahun_pelaksanaan' => '2025',
            'jumlah_dana' => -1000,
        ];
        $rules = (new PenelitianRequest)->rules();
        $validator = Validator::make($data, $rules);

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('jumlah_dana', $validator->errors()->toArray());
    }

    private function makeFileUploadRequest(): FileUploadRequest
    {
        $request = new FileUploadRequest;
        $routeMock = $this->createMock(Route::class);
        $routeMock->method('parameter')->with('field', 'file')->willReturn('file');
        $request->setRouteResolver(fn () => $routeMock);

        return $request;
    }

    public function test_file_upload_request_passes_with_valid_image(): void
    {
        $request = $this->makeFileUploadRequest();
        $data = [
            'file' => UploadedFile::fake()->image('logo.png', 100, 100),
        ];
        $rules = $request->rules();
        $validator = Validator::make($data, $rules);

        $this->assertTrue($validator->passes());
    }

    public function test_file_upload_request_fails_without_file(): void
    {
        $request = $this->makeFileUploadRequest();
        $data = [];
        $rules = $request->rules();
        $validator = Validator::make($data, $rules);

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('file', $validator->errors()->toArray());
    }

    public function test_file_upload_request_fails_with_non_image(): void
    {
        $request = $this->makeFileUploadRequest();
        $data = [
            'file' => UploadedFile::fake()->create('doc.pdf', 100),
        ];
        $rules = $request->rules();
        $validator = Validator::make($data, $rules);

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('file', $validator->errors()->toArray());
    }

    public function test_fakultas_request_passes_with_valid_data(): void
    {
        $data = ['kode_fakultas' => 'FTIK', 'nama_fakultas' => 'Fakultas Teknik'];
        $rules = (new FakultasRequest)->rules();
        $validator = Validator::make($data, $rules);

        $this->assertTrue($validator->passes());
    }

    public function test_fakultas_request_fails_without_kode(): void
    {
        $data = ['nama_fakultas' => 'Test'];
        $rules = (new FakultasRequest)->rules();
        $validator = Validator::make($data, $rules);

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('kode_fakultas', $validator->errors()->toArray());
    }

    public function test_fakultas_request_fails_with_duplicate_kode(): void
    {
        Fakultas::create(['kode_fakultas' => 'FTIK', 'nama_fakultas' => 'FTIK']);

        $data = ['kode_fakultas' => 'FTIK', 'nama_fakultas' => 'Lain'];
        $rules = (new FakultasRequest)->rules();
        $validator = Validator::make($data, $rules);

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('kode_fakultas', $validator->errors()->toArray());
    }

    public function test_fakultas_request_passes_with_same_kode_on_update(): void
    {
        $fakultas = Fakultas::create(['kode_fakultas' => 'FTIK', 'nama_fakultas' => 'FTIK']);

        $request = new FakultasRequest;
        $routeMock = $this->createMock(Route::class);
        $routeMock->method('parameter')->with('fakultas')->willReturn($fakultas);
        $request->setRouteResolver(fn () => $routeMock);

        $data = ['kode_fakultas' => 'FTIK', 'nama_fakultas' => 'FTIK UPDATED'];
        $rules = $request->rules();
        $validator = Validator::make($data, $rules);

        $this->assertTrue($validator->passes());
    }

    public function test_mata_kuliah_request_passes_with_valid_data(): void
    {
        $prodi = Prodi::factory()->create();

        $data = ['kode_mk' => 'IF101', 'nama_mk' => 'Algoritma', 'prodi_id' => $prodi->id];
        $rules = (new MataKuliahRequest)->rules();
        $validator = Validator::make($data, $rules);

        $this->assertTrue($validator->passes());
    }

    public function test_mata_kuliah_request_fails_without_kode(): void
    {
        $data = ['nama_mk' => 'Test', 'prodi_id' => 1];
        $rules = (new MataKuliahRequest)->rules();
        $validator = Validator::make($data, $rules);

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('kode_mk', $validator->errors()->toArray());
    }

    public function test_periode_akademik_request_passes_with_valid_data(): void
    {
        $data = ['kode_periode' => '20251', 'nama_periode' => 'Ganjil 2025/2026'];
        $rules = (new PeriodeAkademikRequest)->rules();
        $validator = Validator::make($data, $rules);

        $this->assertTrue($validator->passes());
    }

    public function test_periode_akademik_request_fails_without_kode(): void
    {
        $data = ['nama_periode' => 'Test'];
        $rules = (new PeriodeAkademikRequest)->rules();
        $validator = Validator::make($data, $rules);

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('kode_periode', $validator->errors()->toArray());
    }

    public function test_lembaga_akreditasi_request_passes_with_valid_data(): void
    {
        $data = ['nama_lembaga' => 'BAN-PT', 'singkatan' => 'BAN-PT'];
        $rules = (new LembagaAkreditasiRequest)->rules();
        $validator = Validator::make($data, $rules);

        $this->assertTrue($validator->passes());
    }

    public function test_lembaga_akreditasi_request_fails_without_nama(): void
    {
        $data = ['singkatan' => 'BAN-PT'];
        $rules = (new LembagaAkreditasiRequest)->rules();
        $validator = Validator::make($data, $rules);

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('nama_lembaga', $validator->errors()->toArray());
    }

    public function test_indikator_akreditasi_request_passes_with_valid_data(): void
    {
        $lembaga = LembagaAkreditasi::create(['nama_lembaga' => 'BAN-PT', 'singkatan' => 'BAN-PT']);
        $instrumen = InstrumenAkreditasi::create(['lembaga_id' => $lembaga->id, 'nama_instrumen' => 'IAPS']);

        $data = [
            'instrumen_id' => $instrumen->id,
            'kode_indikator' => 'A1',
            'nama_indikator' => 'Visi',
            'kriteria' => 'Kriteria A',
            'bobot' => 10,
            'jenis_akreditasi' => 'Unggul',
        ];
        $rules = (new IndikatorAkreditasiRequest)->rules();
        $validator = Validator::make($data, $rules);

        $this->assertTrue($validator->passes());
    }

    public function test_indikator_akreditasi_request_fails_without_instrumen(): void
    {
        $data = ['kode_indikator' => 'A1', 'nama_indikator' => 'Test', 'kriteria' => 'A', 'bobot' => 10, 'jenis_akreditasi' => 'Unggul'];
        $rules = (new IndikatorAkreditasiRequest)->rules();
        $validator = Validator::make($data, $rules);

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('instrumen_id', $validator->errors()->toArray());
    }

    public function test_sarana_request_passes_with_valid_data(): void
    {
        $prodi = Prodi::factory()->create();

        $data = ['prodi_id' => $prodi->id, 'nama_sarana' => 'Lab Komputer', 'jenis_sarana' => 'Lab', 'jumlah' => 30, 'kondisi' => 'baik'];
        $rules = (new SaranaRequest)->rules();
        $validator = Validator::make($data, $rules);

        $this->assertTrue($validator->passes());
    }

    public function test_sarana_request_fails_without_kondisi(): void
    {
        $prodi = Prodi::factory()->create();

        $data = ['prodi_id' => $prodi->id, 'nama_sarana' => 'Lab', 'jenis_sarana' => 'Lab', 'jumlah' => 1];
        $rules = (new SaranaRequest)->rules();
        $validator = Validator::make($data, $rules);

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('kondisi', $validator->errors()->toArray());
    }

    public function test_kerjasama_request_passes_with_valid_data(): void
    {
        $mitra = Mitra::create(['nama_mitra' => 'Mitra Test', 'jenis_mitra' => 'Industri']);
        $prodi = Prodi::factory()->create();

        $data = [
            'mitra_id' => $mitra->id, 'prodi_id' => $prodi->id,
            'jenis_kerjasama' => 'Penelitian', 'nomor_mou' => '001/MOU/2025',
            'tanggal_mulai' => '2025-01-01', 'tanggal_selesai' => '2026-01-01',
        ];
        $rules = (new KerjasamaRequest)->rules();
        $validator = Validator::make($data, $rules);

        $this->assertTrue($validator->passes());
    }

    public function test_kerjasama_request_fails_with_tanggal_selesai_sebelum_mulai(): void
    {
        $mitra = Mitra::create(['nama_mitra' => 'Mitra Test', 'jenis_mitra' => 'Industri']);
        $prodi = Prodi::factory()->create();

        $data = [
            'mitra_id' => $mitra->id, 'prodi_id' => $prodi->id,
            'jenis_kerjasama' => 'Penelitian', 'nomor_mou' => '001/MOU/2025',
            'tanggal_mulai' => '2026-01-01', 'tanggal_selesai' => '2025-01-01',
        ];
        $rules = (new KerjasamaRequest)->rules();
        $validator = Validator::make($data, $rules);

        $this->assertTrue($validator->fails());
    }

    public function test_publikasi_request_passes_with_valid_data(): void
    {
        $prodi = Prodi::factory()->create();
        $dosen = Dosen::factory()->create(['prodi_id' => $prodi->id]);

        $data = [
            'dosen_id' => $dosen->id, 'prodi_id' => $prodi->id,
            'judul_publikasi' => 'Paper AI', 'jenis_publikasi' => 'Jurnal',
            'tingkat' => 'Nasional', 'tahun' => '2025',
        ];
        $rules = (new PublikasiRequest)->rules();
        $validator = Validator::make($data, $rules);

        $this->assertTrue($validator->passes());
    }

    public function test_publikasi_request_fails_without_tahun(): void
    {
        $prodi = Prodi::factory()->create();
        $dosen = Dosen::factory()->create(['prodi_id' => $prodi->id]);

        $data = [
            'dosen_id' => $dosen->id, 'prodi_id' => $prodi->id,
            'judul_publikasi' => 'Paper', 'jenis_publikasi' => 'Jurnal',
            'tingkat' => 'Nasional',
        ];
        $rules = (new PublikasiRequest)->rules();
        $validator = Validator::make($data, $rules);

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('tahun', $validator->errors()->toArray());
    }

    public function test_rps_request_passes_on_store(): void
    {
        $prodi = Prodi::factory()->create();
        $mk = MataKuliah::factory()->create(['prodi_id' => $prodi->id]);

        $data = ['mata_kuliah_id' => $mk->id, 'prodi_id' => $prodi->id];
        $request = new RpsRequest;
        $request->setMethod('POST');
        $validator = Validator::make($data, $request->rules());

        $this->assertTrue($validator->passes());
    }

    public function test_rps_request_requires_status_on_update(): void
    {
        $prodi = Prodi::factory()->create();
        $mk = MataKuliah::factory()->create(['prodi_id' => $prodi->id]);

        $data = ['mata_kuliah_id' => $mk->id, 'prodi_id' => $prodi->id];
        $request = new RpsRequest;
        $request->setMethod('PUT');
        $validator = Validator::make($data, $request->rules());

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('status', $validator->errors()->toArray());
    }

    public function test_dokumen_bukti_request_requires_file_on_store(): void
    {
        $data = ['nama_dokumen' => 'Test'];
        $request = new DokumenBuktiRequest;
        $request->setMethod('POST');
        $rules = $request->rules();
        $validator = Validator::make($data, $rules);

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('file', $validator->errors()->toArray());
    }

    public function test_dokumen_bukti_request_accepts_no_file_on_update(): void
    {
        $data = ['nama_dokumen' => 'Test'];
        $request = new DokumenBuktiRequest;
        $request->setMethod('PUT');
        $rules = $request->rules();
        $validator = Validator::make($data, $rules);

        $this->assertTrue($validator->passes());
    }

    public function test_audit_mutu_request_requires_status_on_update(): void
    {
        $prodi = Prodi::factory()->create();
        $periode = PeriodeAkademik::factory()->create();

        $data = ['prodi_id' => $prodi->id, 'periode_id' => $periode->id, 'judul_audit' => 'Audit Mutu', 'tanggal_audit' => '2025-01-01'];
        $request = new AuditMutuRequest;
        $request->setMethod('PUT');
        $validator = Validator::make($data, $request->rules());

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('status', $validator->errors()->toArray());
    }

    public function test_audit_mutu_request_accepts_no_status_on_store(): void
    {
        $prodi = Prodi::factory()->create();
        $periode = PeriodeAkademik::factory()->create();

        $data = ['prodi_id' => $prodi->id, 'periode_id' => $periode->id, 'judul_audit' => 'Audit Mutu', 'tanggal_audit' => '2025-01-01'];
        $request = new AuditMutuRequest;
        $request->setMethod('POST');
        $validator = Validator::make($data, $request->rules());

        $this->assertTrue($validator->passes());
    }

    public function test_risk_register_request_requires_status_on_update(): void
    {
        $prodi = Prodi::factory()->create();
        $periode = PeriodeAkademik::factory()->create();

        $data = ['prodi_id' => $prodi->id, 'periode_id' => $periode->id, 'nama_risiko' => 'Risiko A', 'dampak' => 'tinggi', 'probabilitas' => 'sedang', 'skor_risiko' => '15'];
        $request = new RiskRegisterRequest;
        $request->setMethod('PUT');
        $validator = Validator::make($data, $request->rules());

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('status', $validator->errors()->toArray());
    }

    public function test_risk_register_request_accepts_valid_store_data(): void
    {
        $prodi = Prodi::factory()->create();
        $periode = PeriodeAkademik::factory()->create();

        $data = ['prodi_id' => $prodi->id, 'periode_id' => $periode->id, 'nama_risiko' => 'Risiko A', 'kategori' => 'Operasional', 'dampak' => 'tinggi', 'probabilitas' => 'sedang', 'skor_risiko' => '15'];
        $request = new RiskRegisterRequest;
        $request->setMethod('POST');
        $validator = Validator::make($data, $request->rules());

        $this->assertTrue($validator->passes());
    }

    public function test_risk_register_request_requires_skor_risiko_on_store(): void
    {
        $prodi = Prodi::factory()->create();
        $periode = PeriodeAkademik::factory()->create();

        $data = ['prodi_id' => $prodi->id, 'periode_id' => $periode->id, 'nama_risiko' => 'Risiko A', 'dampak' => 'tinggi', 'probabilitas' => 'sedang'];
        $request = new RiskRegisterRequest;
        $request->setMethod('POST');
        $validator = Validator::make($data, $request->rules());

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('skor_risiko', $validator->errors()->toArray());
    }

    public function test_agent_run_request_passes_with_valid_data(): void
    {
        $prodi = Prodi::factory()->create();
        $fakultas = Fakultas::factory()->create();

        $data = [
            'prodi_id' => $prodi->id,
            'fakultas_id' => $fakultas->id,
            'periode' => '2024/2025',
            'filter' => ['status' => 'active'],
            'options' => ['detail' => true],
        ];

        $request = new AgentRunRequest;
        $request->setMethod('POST');
        $validator = Validator::make($data, $request->rules());

        $this->assertTrue($validator->passes());
    }

    public function test_agent_run_request_fails_with_invalid_prodi_id(): void
    {
        $data = ['prodi_id' => 99999]; // non-existent

        $request = new AgentRunRequest;
        $request->setMethod('POST');
        $validator = Validator::make($data, $request->rules());

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('prodi_id', $validator->errors()->toArray());
    }

    public function test_agent_run_request_fails_with_invalid_fakultas_id(): void
    {
        $data = ['fakultas_id' => 99999];

        $request = new AgentRunRequest;
        $request->setMethod('POST');
        $validator = Validator::make($data, $request->rules());

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('fakultas_id', $validator->errors()->toArray());
    }

    public function test_agent_run_request_periode_max_validation(): void
    {
        $data = ['periode' => str_repeat('a', 21)]; // >20 chars

        $request = new AgentRunRequest;
        $request->setMethod('POST');
        $validator = Validator::make($data, $request->rules());

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('periode', $validator->errors()->toArray());
    }

    public function test_agent_run_request_filter_must_be_array(): void
    {
        $data = ['filter' => 'not-an-array'];

        $request = new AgentRunRequest;
        $request->setMethod('POST');
        $validator = Validator::make($data, $request->rules());

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('filter', $validator->errors()->toArray());
    }

    public function test_agent_run_request_options_must_be_array(): void
    {
        $data = ['options' => 'not-an-array'];

        $request = new AgentRunRequest;
        $request->setMethod('POST');
        $validator = Validator::make($data, $request->rules());

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('options', $validator->errors()->toArray());
    }

    public function test_agent_run_request_custom_messages(): void
    {
        $request = new AgentRunRequest;
        $messages = $request->messages();

        $this->assertArrayHasKey('prodi_id.exists', $messages);
        $this->assertSame('Program studi tidak ditemukan.', $messages['prodi_id.exists']);

        $this->assertArrayHasKey('fakultas_id.exists', $messages);
        $this->assertSame('Fakultas tidak ditemukan.', $messages['fakultas_id.exists']);

        $this->assertArrayHasKey('periode.max', $messages);
        $this->assertSame('Periode maksimal 20 karakter.', $messages['periode.max']);
    }
}
