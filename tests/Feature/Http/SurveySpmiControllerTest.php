<?php

namespace Tests\Feature\Http;

use App\Models\PeriodeAkademik;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\SeedOnce;
use Tests\TestCase;

class SurveySpmiControllerTest extends TestCase
{
    use RefreshDatabase, SeedOnce;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedOnce();
    }

    public function test_unauthenticated_redirects_to_login(): void
    {
        $this->get(route('spmi.survey'))
            ->assertRedirect(route('login'));
    }

    public function test_unauthorized_user_gets_403(): void
    {
        $user = $this->dosen();

        $this->actingAs($user)
            ->get(route('spmi.survey'))
            ->assertStatus(403);
    }

    public function test_authorized_user_can_access_index(): void
    {
        $user = $this->admin();

        $this->actingAs($user)
            ->get(route('spmi.survey'))
            ->assertStatus(200);
    }

    public function test_store_creates_survey(): void
    {
        $user = $this->admin();
        $periode = PeriodeAkademik::factory()->create();

        $response = $this->actingAs($user)
            ->post(route('spmi.survey.store'), [
                'periode_id' => $periode->id,
                'responden_type' => 'dosen',
            ]);

        $response->assertSessionHas('success');
        $response->assertRedirect();

        $this->assertDatabaseHas('trx_survey_spmi', [
            'periode_id' => $periode->id,
            'responden_type' => 'dosen',
        ]);
    }

    public function test_store_validates_required_fields(): void
    {
        $user = $this->admin();

        $response = $this->actingAs($user)
            ->post(route('spmi.survey.store'), []);

        $response->assertSessionHasErrors(['periode_id', 'responden_type']);
    }
}
