<?php

namespace Tests\Feature\Controllers;

use App\Models\Prodi;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\Feature\SeedOnce;
use Tests\TestCase;

class DataImportControllerTest extends TestCase
{
    use RefreshDatabase, SeedOnce;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedOnce();
    }

    // ─── AUTHENTICATION ───────────────────────────────────────

    public function test_unauthenticated_user_cannot_access_templates(): void
    {
        $response = $this->get(route('data-import.templates'));
        $response->assertStatus(302);
    }

    public function test_unauthenticated_user_cannot_access_upload(): void
    {
        $response = $this->post(route('data-import.upload'));
        $response->assertStatus(302);
    }

    // ─── TEMPLATES ────────────────────────────────────────────

    public function test_templates_page_loads(): void
    {
        $response = $this->actingAs($this->admin())
            ->get(route('data-import.templates'));

        $response->assertStatus(200);
    }

    public function test_download_template_returns_file(): void
    {
        Storage::fake('local');

        $response = $this->actingAs($this->admin())
            ->get(route('data-import.templates.download', ['type' => 'dosen']));

        $response->assertStatus(200);
        $response->assertDownload('template_dosen.xlsx');
    }

    public function test_download_template_for_mahasiswa(): void
    {
        Storage::fake('local');

        $response = $this->actingAs($this->admin())
            ->get(route('data-import.templates.download', ['type' => 'mahasiswa']));

        $response->assertStatus(200);
        $response->assertDownload('template_mahasiswa.xlsx');
    }

    public function test_download_template_returns_404_for_invalid_type(): void
    {
        $response = $this->actingAs($this->admin())
            ->get(route('data-import.templates.download', ['type' => 'invalid_type']));

        $response->assertStatus(404);
    }

    // ─── UPLOAD ───────────────────────────────────────────────

    public function test_upload_validates_file_type(): void
    {
        Storage::fake('local');

        $file = UploadedFile::fake()->create('document.pdf', 100);

        $response = $this->actingAs($this->admin())
            ->from(route('data-import.templates'))
            ->post(route('data-import.upload'), [
                'type' => 'dosen',
                'file' => $file,
            ]);

        $response->assertStatus(302);
        $response->assertSessionHasErrors('file');
    }

    public function test_upload_validates_file_extension(): void
    {
        Storage::fake('local');

        $file = UploadedFile::fake()->create('data.txt', 100);

        $response = $this->actingAs($this->admin())
            ->from(route('data-import.templates'))
            ->post(route('data-import.upload'), [
                'type' => 'dosen',
                'file' => $file,
            ]);

        $response->assertStatus(302);
        $response->assertSessionHasErrors('file');
    }

    public function test_upload_validates_file_mime(): void
    {
        Storage::fake('local');

        $file = UploadedFile::fake()->create('data.exe', 100);

        $response = $this->actingAs($this->admin())
            ->from(route('data-import.templates'))
            ->post(route('data-import.upload'), [
                'type' => 'dosen',
                'file' => $file,
            ]);

        $response->assertStatus(302);
        $response->assertSessionHasErrors('file');
    }

    public function test_upload_validates_type_parameter(): void
    {
        Storage::fake('local');

        $file = UploadedFile::fake()->create('data.xlsx', 100);

        $response = $this->actingAs($this->admin())
            ->from(route('data-import.templates'))
            ->post(route('data-import.upload'), [
                'type' => 'invalid_type',
                'file' => $file,
            ]);

        $response->assertStatus(302);
        $response->assertSessionHasErrors('type');
    }

    public function test_upload_validates_required_fields(): void
    {
        Storage::fake('local');

        $response = $this->actingAs($this->admin())
            ->from(route('data-import.templates'))
            ->post(route('data-import.upload'), []);

        $response->assertStatus(302);
        $response->assertSessionHasErrors(['type', 'file']);
    }

    public function test_upload_validates_file_size(): void
    {
        Storage::fake('local');

        $file = UploadedFile::fake()->create('large.xlsx', 6000); // > 5120 KB

        $response = $this->actingAs($this->admin())
            ->from(route('data-import.templates'))
            ->post(route('data-import.upload'), [
                'type' => 'dosen',
                'file' => $file,
            ]);

        $response->assertStatus(302);
        $response->assertSessionHasErrors('file');
    }

    // ─── UNAUTHORIZED ─────────────────────────────────────────

    public function test_unauthorized_user_cannot_download_template(): void
    {
        $noPermUser = User::factory()->create(['is_active' => true]);

        $response = $this->actingAs($noPermUser)
            ->get(route('data-import.templates'));

        $response->assertStatus(403);
    }
}
