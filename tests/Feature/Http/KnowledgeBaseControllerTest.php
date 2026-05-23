<?php

namespace Tests\Feature\Http;

use App\Models\KnowledgeBaseCategory;
use App\Models\KnowledgeBaseDocument;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\SeedRolePermission;
use Tests\TestCase;

class KnowledgeBaseControllerTest extends TestCase
{
    use RefreshDatabase, SeedRolePermission;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedPermissions();
    }

    public function test_index_returns_documents(): void
    {
        KnowledgeBaseCategory::factory()->create();
        KnowledgeBaseDocument::factory()->count(3)->create();

        $response = $this->actingAs($this->admin())->get(route('admin.knowledge-base.index'));
        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page->component('Admin/KnowledgeBase/Index'));
    }

    public function test_destroy_removes_document(): void
    {
        KnowledgeBaseCategory::factory()->create();
        $doc = KnowledgeBaseDocument::factory()->create();

        $response = $this->actingAs($this->admin())->delete(route('admin.knowledge-base.destroy', $doc->id));
        $response->assertStatus(302);
        $this->assertDatabaseMissing('knowledge_base_documents', ['id' => $doc->id]);
    }

    public function test_ask_returns_answer(): void
    {
        $response = $this->actingAs($this->admin())->postJson('/api/rag/ask', [
            'question' => 'Apa itu akreditasi?',
        ]);

        $response->assertStatus(200);
    }

    public function test_ask_validates_question(): void
    {
        $response = $this->actingAs($this->admin())->postJson('/api/rag/ask', []);
        $response->assertStatus(422);
    }
}
