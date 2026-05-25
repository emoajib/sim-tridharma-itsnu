<?php

namespace Tests\Unit\Services;

use App\Services\SPMI\SpmiWorkflowService;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class SpmiWorkflowServiceTest extends TestCase
{
    private SpmiWorkflowService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new SpmiWorkflowService();
    }

    #[Test]
    public function it_allows_valid_transition()
    {
        $this->assertTrue($this->service->canTransition('draft', 'submitted'));
        $this->assertTrue($this->service->canTransition('submitted', 'assigned'));
        $this->assertTrue($this->service->canTransition('in_progress', 'awaiting_verification'));
        $this->assertTrue($this->service->canTransition('awaiting_verification', 'verified'));
        $this->assertTrue($this->service->canTransition('verified', 'closed'));
    }

    #[Test]
    public function it_blocks_invalid_transition()
    {
        $this->assertFalse($this->service->canTransition('draft', 'closed'));
        $this->assertFalse($this->service->canTransition('closed', 'in_progress'));
        $this->assertFalse($this->service->canTransition('verified', 'draft'));
        $this->assertFalse($this->service->canTransition('archived', 'closed'));
    }

    #[Test]
    public function it_blocks_transition_from_archived()
    {
        $this->assertFalse($this->service->canTransition('archived', 'closed'));
        $this->assertFalse($this->service->canTransition('archived', 'draft'));
        $this->assertFalse($this->service->canTransition('archived', 'in_progress'));
    }

    #[Test]
    public function it_allows_transition_from_rejected_to_in_progress()
    {
        $this->assertTrue($this->service->canTransition('rejected', 'in_progress'));
    }

    #[Test]
    public function it_handles_capa_workflow()
    {
        $this->assertTrue($this->service->canTransition('open', 'in_progress', 'capa'));
        $this->assertTrue($this->service->canTransition('awaiting_verification', 'verified', 'capa'));
        $this->assertFalse($this->service->canTransition('verified', 'in_progress', 'capa'));
    }
}
