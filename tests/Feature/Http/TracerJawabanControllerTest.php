<?php

namespace Tests\Feature\Http;

use App\Models\Alumni;
use App\Models\KuisionerTracer;
use App\Models\Prodi;
use App\Models\TracerJawaban;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\SeedOnce;
use Tests\TestCase;

class TracerJawabanControllerTest extends TestCase
{
    use RefreshDatabase, SeedOnce;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedOnce();
    }

    public function test_unauthenticated_user_redirected_from_index(): void
    {
        $response = $this->get(route('tracer.jawaban'));
        $response->assertStatus(302);
    }

    public function test_unauthenticated_user_redirected_from_store(): void
    {
        $response = $this->post(route('tracer.jawaban.store'));
        $response->assertStatus(302);
    }

    public function test_unauthenticated_user_redirected_from_destroy(): void
    {
        $response = $this->delete(route('tracer.jawaban.destroy', 1));
        $response->assertStatus(302);
    }

    public function test_unauthorized_user_gets_403_on_index(): void
    {
        $user = User::factory()->create(['is_active' => true]);
        $response = $this->actingAs($user)->get(route('tracer.jawaban'));
        $response->assertStatus(403);
    }

    public function test_unauthorized_user_gets_403_on_store(): void
    {
        $user = User::factory()->create(['is_active' => true]);
        $response = $this->actingAs($user)->post(route('tracer.jawaban.store'));
        $response->assertStatus(403);
    }

    public function test_index_returns_200_for_authorized_user(): void
    {
        Prodi::factory()->create();

        $response = $this->actingAs($this->admin())->get(route('tracer.jawaban'));
        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page->component('Tracer/Jawaban/Index'));
    }

    public function test_store_creates_record_and_redirects(): void
    {
        Prodi::factory()->create();
        $alumni = Alumni::create([
            'nim' => '123456',
            'nama' => 'Test Alumni',
            'prodi_id' => 1,
            'tahun_lulus' => 2024,
            'is_active' => true,
        ]);
        $kuisioner = KuisionerTracer::create([
            'prodi_id' => 1,
            'judul_kuisioner' => 'Test Kuisioner',
            'tahun' => 2024,
            'pertanyaan' => json_encode(['q1' => 'Question 1']),
            'is_active' => true,
        ]);

        $response = $this->actingAs($this->admin())
            ->from(route('tracer.jawaban'))
            ->post(route('tracer.jawaban.store'), [
                'alumni_id' => $alumni->id,
                'kuisioner_id' => $kuisioner->id,
                'jawaban' => json_encode(['q1' => 'Answer 1']),
                'diisi_pada' => now()->toDateTimeString(),
            ]);

        $response->assertStatus(302);
        $response->assertSessionHas('success');
        $this->assertDatabaseHas('trx_tracer_jawaban', [
            'alumni_id' => $alumni->id,
            'kuisioner_id' => $kuisioner->id,
        ]);
    }

    public function test_store_fails_validation_with_missing_fields(): void
    {
        $response = $this->actingAs($this->admin())
            ->from(route('tracer.jawaban'))
            ->post(route('tracer.jawaban.store'), []);

        $response->assertStatus(302);
        $response->assertSessionHasErrors(['alumni_id', 'kuisioner_id', 'jawaban']);
    }

    public function test_destroy_soft_deletes_record(): void
    {
        Prodi::factory()->create();
        $alumni = Alumni::create([
            'nim' => '123456',
            'nama' => 'Test Alumni',
            'prodi_id' => 1,
            'tahun_lulus' => 2024,
            'is_active' => true,
        ]);
        $kuisioner = KuisionerTracer::create([
            'prodi_id' => 1,
            'judul_kuisioner' => 'Test Kuisioner',
            'tahun' => 2024,
            'pertanyaan' => json_encode(['q1' => 'Question 1']),
            'is_active' => true,
        ]);

        $jawaban = TracerJawaban::create([
            'alumni_id' => $alumni->id,
            'kuisioner_id' => $kuisioner->id,
            'jawaban' => json_encode(['q1' => 'Answer 1']),
            'diisi_pada' => now(),
        ]);

        $response = $this->actingAs($this->admin())
            ->delete(route('tracer.jawaban.destroy', $jawaban));

        $response->assertStatus(302);
        $response->assertSessionHas('success');
        $this->assertSoftDeleted($jawaban);
    }
}
