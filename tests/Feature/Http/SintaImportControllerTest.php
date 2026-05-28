<?php

namespace Tests\Feature\Http;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\Feature\SeedOnce;
use Tests\TestCase;

class SintaImportControllerTest extends TestCase
{
    use RefreshDatabase, SeedOnce;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedOnce();
    }

    public function test_unauthenticated_user_redirected_from_import_penelitian(): void
    {
        $response = $this->post(route('import.sinta.penelitian'));
        $response->assertStatus(302);
    }

    public function test_unauthenticated_user_redirected_from_import_publikasi(): void
    {
        $response = $this->post(route('import.sinta.publikasi'));
        $response->assertStatus(302);
    }

    public function test_unauthenticated_user_redirected_from_import_pkm(): void
    {
        $response = $this->post(route('import.sinta.pkm'));
        $response->assertStatus(302);
    }

    public function test_unauthorized_user_gets_403_on_import_penelitian(): void
    {
        $user = User::factory()->create(['is_active' => true]);
        $response = $this->actingAs($user)->post(route('import.sinta.penelitian'));
        $response->assertStatus(403);
    }

    public function test_unauthorized_user_gets_403_on_import_publikasi(): void
    {
        $user = User::factory()->create(['is_active' => true]);
        $response = $this->actingAs($user)->post(route('import.sinta.publikasi'));
        $response->assertStatus(403);
    }

    public function test_unauthorized_user_gets_403_on_import_pkm(): void
    {
        $user = User::factory()->create(['is_active' => true]);
        $response = $this->actingAs($user)->post(route('import.sinta.pkm'));
        $response->assertStatus(403);
    }

    public function test_import_penelitian_without_file_fails_validation(): void
    {
        $response = $this->actingAs($this->admin())
            ->from(route('integrasi'))
            ->post(route('import.sinta.penelitian'), []);

        $response->assertStatus(302);
        $response->assertSessionHasErrors(['file']);
    }

    public function test_import_penelitian_with_invalid_file_fails_validation(): void
    {
        $file = UploadedFile::fake()->create('document.pdf', 100);

        $response = $this->actingAs($this->admin())
            ->from(route('integrasi'))
            ->post(route('import.sinta.penelitian'), [
                'file' => $file,
            ]);

        $response->assertStatus(302);
        $response->assertSessionHasErrors(['file']);
    }

    public function test_import_publikasi_without_file_fails_validation(): void
    {
        $response = $this->actingAs($this->admin())
            ->from(route('integrasi'))
            ->post(route('import.sinta.publikasi'), []);

        $response->assertStatus(302);
        $response->assertSessionHasErrors(['file']);
    }

    public function test_import_publikasi_with_invalid_file_fails_validation(): void
    {
        $file = UploadedFile::fake()->create('document.pdf', 100);

        $response = $this->actingAs($this->admin())
            ->from(route('integrasi'))
            ->post(route('import.sinta.publikasi'), [
                'file' => $file,
            ]);

        $response->assertStatus(302);
        $response->assertSessionHasErrors(['file']);
    }

    public function test_import_pkm_without_file_fails_validation(): void
    {
        $response = $this->actingAs($this->admin())
            ->from(route('integrasi'))
            ->post(route('import.sinta.pkm'), []);

        $response->assertStatus(302);
        $response->assertSessionHasErrors(['file']);
    }

    public function test_import_pkm_with_invalid_file_fails_validation(): void
    {
        $file = UploadedFile::fake()->create('document.pdf', 100);

        $response = $this->actingAs($this->admin())
            ->from(route('integrasi'))
            ->post(route('import.sinta.pkm'), [
                'file' => $file,
            ]);

        $response->assertStatus(302);
        $response->assertSessionHasErrors(['file']);
    }
}
