<?php

namespace App\Jobs;

use App\Models\KnowledgeBaseDocument;
use App\Services\KnowledgeBase\DocumentProcessingService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

/**
 * Vetted by AI - Manual Review Required by Senior Engineer/Manager
 */
class ProcessKnowledgeBaseDocument implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of seconds the job can run before timing out.
     *
     * @var int
     */
    public $timeout = 600; // 10 minutes for heavy embedding tasks

    /**
     * Create a new job instance.
     */
    public function __construct(
        protected KnowledgeBaseDocument $document
    ) {}

    /**
     * Execute the job.
     */
    public function handle(DocumentProcessingService $processor): void
    {
        Log::info("Memulai pemrosesan latar belakang untuk dokumen: {$this->document->judul}");

        try {
            $this->document->update(['status' => 'processing']);
            
            $result = $processor->process($this->document);

            if ($result['success']) {
                Log::info("Pemrosesan dokumen '{$this->document->judul}' selesai: {$result['chunk_count']} chunk dibuat.");
            } else {
                Log::error("Gagal memproses dokumen '{$this->document->judul}': {$result['reason']}");
            }
        } catch (\Exception $e) {
            Log::error("Eksepsi saat memproses dokumen '{$this->document->judul}': " . $e->getMessage());
            $this->document->update(['status' => 'error']);
            throw $e;
        }
    }
}
