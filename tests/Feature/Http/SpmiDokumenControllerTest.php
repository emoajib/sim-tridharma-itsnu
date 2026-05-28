<?php

namespace Tests\Feature\Http;

use App\Models\SpmiDokumen;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\Feature\SeedOnce;
use Tests\TestCase;

class SpmiDokumenControllerTest extends TestCase
{
    use RefreshDatabase, SeedOnce;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedOnce();
    }

    public function test_unauthenticated_redirects_to_login(): void
    {
        $this->get(route('spmi.dokumen-mutu'))
            ->assertRedirect(route('login'));
    }

    public function test_unauthorized_user_gets_403(): void
    {
        $user = $this->dosen();

        $this->actingAs($user)
            ->get(route('spmi.dokumen-mutu'))
            ->assertStatus(403);
    }

    public function test_authorized_user_can_access_index(): void
    {
        $user = $this->admin();

        $this->actingAs($user)
            ->get(route('spmi.dokumen-mutu'))
            ->assertStatus(200);
    }

    public function test_store_creates_dokumen(): void
    {
        $user = $this->admin();

        $response = $this->actingAs($user)
            ->post(route('spmi.dokumen-mutu.store'), [
                'kategori' => 'Akademik',
                'nomor_dokumen' => 'DOC-001',
                'judul' => 'Dokumen Mutu Test',
                'file' => UploadedFile::fake()->create('test.pdf', 100),
            ]);

        $response->assertSessionHas('success');
        $response->assertRedirect();

        $this->assertDatabaseHas('m_spmi_dokumen', [
            'nomor_dokumen' => 'DOC-001',
            'judul' => 'Dokumen Mutu Test',
        ]);
    }

    public function test_store_validates_required_fields(): void
    {
        $user = $this->admin();

        $response = $this->actingAs($user)
            ->post(route('spmi.dokumen-mutu.store'), []);

        $response->assertSessionHasErrors(['kategori', 'nomor_dokumen', 'judul', 'file']);
    }

    public function test_update_updates_dokumen(): void
    {
        $user = $this->admin();
        $dokumen = SpmiDokumen::factory()->create();

        $response = $this->actingAs($user)
            ->put(route('spmi.dokumen-mutu.update', $dokumen), [
                'judul' => 'Updated Title',
            ]);

        $response->assertSessionHas('success');
        $response->assertRedirect();

        $this->assertDatabaseHas('m_spmi_dokumen', [
            'id' => $dokumen->id,
            'judul' => 'Updated Title',
        ]);
    }

    public function test_destroy_deletes_dokumen(): void
    {
        $user = $this->admin();
        $dokumen = SpmiDokumen::factory()->create();

        $response = $this->actingAs($user)
            ->delete(route('spmi.dokumen-mutu.destroy', $dokumen));

        $response->assertSessionHas('success');
        $response->assertRedirect();

        $this->assertSoftDeleted('m_spmi_dokumen', ['id' => $dokumen->id]);
    }
}
