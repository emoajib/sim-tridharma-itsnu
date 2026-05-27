<?php

namespace Tests\Feature\Controllers;

use App\Models\IndikatorAkreditasi;
use App\Models\InstrumenAkreditasi;
use App\Models\LembagaAkreditasi;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\SeedOnce;
use Tests\TestCase;

class IndikatorAkreditasiControllerTest extends TestCase
{
    use RefreshDatabase, SeedOnce;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedOnce();
    }

    protected function createInstrumen(): InstrumenAkreditasi
    {
        $lembaga = LembagaAkreditasi::factory()->create();

        return InstrumenAkreditasi::factory()->create([
            'lembaga_id' => $lembaga->id,
        ]);
    }

    protected function createIndikator(array $overrides = []): IndikatorAkreditasi
    {
        $instrumen = $this->createInstrumen();

        return IndikatorAkreditasi::create(array_merge([
            'kode_indikator' => 'IND-' . fake()->unique()->bothify('####'),
            'nama_indikator' => fake()->sentence(3),
            'kriteria' => 'VISI',
            'bobot' => 10,
            'jenis_akreditasi' => 'Unggul',
            'instrumen_id' => $instrumen->id,
        ], $overrides));
    }

    // ─── AUTHENTICATION ───────────────────────────────────────

    public function test_unauthenticated_user_redirected_to_login(): void
    {
        $response = $this->get(route('admin.indikator.index'));
        $response->assertStatus(302);
    }

    public function test_unauthorized_user_gets_403(): void
    {
        $response = $this->actingAs($this->dosen())->get(route('admin.indikator.index'));
        $response->assertStatus(403);
    }

    // ─── INDEX ────────────────────────────────────────────────

    public function test_can_list_indikators(): void
    {
        $this->createIndikator();
        $this->createIndikator();
        $this->createIndikator();

        $response = $this->actingAs($this->admin())->get(route('admin.indikator.index'));

        $response->assertStatus(200);
    }

    public function test_index_can_search_by_nama_or_kode(): void
    {
        $instrumen = $this->createInstrumen();
        IndikatorAkreditasi::create([
            'kode_indikator' => 'VISI-001',
            'nama_indikator' => 'Visi dan Misi',
            'kriteria' => 'VISI',
            'bobot' => 15,
            'jenis_akreditasi' => 'Unggul',
            'instrumen_id' => $instrumen->id,
        ]);
        IndikatorAkreditasi::create([
            'kode_indikator' => 'MHS-002',
            'nama_indikator' => 'Mahasiswa',
            'kriteria' => 'MHS',
            'bobot' => 10,
            'jenis_akreditasi' => 'Unggul',
            'instrumen_id' => $instrumen->id,
        ]);

        $response = $this->actingAs($this->admin())
            ->get(route('admin.indikator.index', ['search' => 'Visi']));

        $response->assertStatus(200);
    }

    public function test_index_can_filter_by_instrumen(): void
    {
        $instrumen1 = $this->createInstrumen();
        $instrumen2 = $this->createInstrumen();

        IndikatorAkreditasi::create([
            'kode_indikator' => 'IND-001',
            'nama_indikator' => 'Indikator 1',
            'kriteria' => 'VISI',
            'bobot' => 10,
            'jenis_akreditasi' => 'Unggul',
            'instrumen_id' => $instrumen1->id,
        ]);
        IndikatorAkreditasi::create([
            'kode_indikator' => 'IND-002',
            'nama_indikator' => 'Indikator 2',
            'kriteria' => 'MHS',
            'bobot' => 15,
            'jenis_akreditasi' => 'Baik',
            'instrumen_id' => $instrumen2->id,
        ]);

        $response = $this->actingAs($this->admin())
            ->get(route('admin.indikator.index', ['instrumen_id' => $instrumen1->id]));

        $response->assertStatus(200);
    }

    // ─── STORE ────────────────────────────────────────────────

    public function test_can_create_indikator(): void
    {
        $instrumen = $this->createInstrumen();

        $data = [
            'kode_indikator' => 'VISI-001',
            'nama_indikator' => 'Visi, Misi, Tujuan dan Strategi',
            'kriteria' => 'VISI',
            'bobot' => 15.00,
            'target' => 'Unggul',
            'jenis_akreditasi' => 'Unggul',
            'instrumen_id' => $instrumen->id,
        ];

        $response = $this->actingAs($this->admin())
            ->from(route('admin.indikator.index'))
            ->post(route('admin.indikator.store'), $data);

        $response->assertStatus(302);
        $response->assertSessionHas('success');
        $this->assertDatabaseHas('m_indikator_akreditasi', [
            'kode_indikator' => 'VISI-001',
            'nama_indikator' => 'Visi, Misi, Tujuan dan Strategi',
            'bobot' => 15.00,
        ]);
    }

    // ─── UPDATE ───────────────────────────────────────────────

    public function test_can_update_indikator(): void
    {
        $instrumen = $this->createInstrumen();
        $indikator = $this->createIndikator(['instrumen_id' => $instrumen->id]);

        $response = $this->actingAs($this->admin())
            ->from(route('admin.indikator.index'))
            ->put(route('admin.indikator.update', $indikator), [
                'kode_indikator' => 'UPDATED-KODE-001',
                'nama_indikator' => 'Visi yang Diperbarui',
                'kriteria' => 'VISI',
                'bobot' => 20.00,
                'jenis_akreditasi' => 'Unggul',
                'instrumen_id' => $instrumen->id,
            ]);

        $response->assertStatus(302);
        $response->assertSessionHas('success');
        $this->assertDatabaseHas('m_indikator_akreditasi', [
            'id' => $indikator->id,
            'kode_indikator' => 'UPDATED-KODE-001',
            'nama_indikator' => 'Visi yang Diperbarui',
            'bobot' => 20.00,
        ]);
    }

    // ─── DELETE ───────────────────────────────────────────────

    public function test_can_delete_indikator(): void
    {
        $indikator = $this->createIndikator();

        $response = $this->actingAs($this->admin())
            ->delete(route('admin.indikator.destroy', $indikator));

        $response->assertStatus(302);
        $response->assertSessionHas('success');
        $this->assertSoftDeleted($indikator);
    }

    // ─── VALIDATION ───────────────────────────────────────────

    public function test_validation_required_fields(): void
    {
        $response = $this->actingAs($this->admin())
            ->from(route('admin.indikator.index'))
            ->post(route('admin.indikator.store'), []);

        // Inertia converts validation redirects to 200 responses
        // with errors embedded in the page data
        $response->assertSessionHasErrors([
            'instrumen_id',
            'kode_indikator',
            'nama_indikator',
            'kriteria',
            'bobot',
            'jenis_akreditasi',
        ]);
    }

    public function test_validation_unique_kode(): void
    {
        $this->createIndikator(['kode_indikator' => 'DUPLICATE-001']);

        $response = $this->actingAs($this->admin())
            ->from(route('admin.indikator.index'))
            ->post(route('admin.indikator.store'), [
                'kode_indikator' => 'DUPLICATE-001',
                'nama_indikator' => 'Duplikasi',
                'kriteria' => 'VISI',
                'bobot' => 10,
                'jenis_akreditasi' => 'Unggul',
                'instrumen_id' => $this->createInstrumen()->id,
            ]);

        $response->assertSessionHasErrors('kode_indikator');
    }

    public function test_validation_bobot_must_be_numeric(): void
    {
        $instrumen = $this->createInstrumen();

        $response = $this->actingAs($this->admin())
            ->from(route('admin.indikator.index'))
            ->post(route('admin.indikator.store'), [
                'kode_indikator' => 'TEST-001',
                'nama_indikator' => 'Test',
                'kriteria' => 'VISI',
                'bobot' => 'not-a-number',
                'jenis_akreditasi' => 'Unggul',
                'instrumen_id' => $instrumen->id,
            ]);

        $response->assertSessionHasErrors('bobot');
    }
}
