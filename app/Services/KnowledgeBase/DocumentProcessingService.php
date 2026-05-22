<?php

namespace App\Services\KnowledgeBase;

use App\Models\KnowledgeBaseDocument;
use App\Services\AI\ChunkerService;
use App\Services\AI\EmbeddingService;
use App\Services\AI\PDFParserService;

class DocumentProcessingService
{
    public function process(KnowledgeBaseDocument $document): array
    {
        $parser = app(PDFParserService::class);
        $result = $parser->extractText($document->file_path);

        $document->update(['page_count' => $result['page_count']]);

        if (empty(trim($result['text']))) {
            $document->update(['status' => 'error']);

            return ['success' => false, 'reason' => 'empty_text'];
        }

        $chunker = app(ChunkerService::class);
        $chunks = $chunker->chunk($result['text']);

        $embeddingService = app(EmbeddingService::class);
        $embeddings = $embeddingService->embed($chunks);

        foreach ($chunks as $i => $content) {
            $document->chunks()->create([
                'chunk_index' => $i,
                'content' => $content,
                'embedding' => $embeddings[$i] ?? null,
            ]);
        }

        $document->update(['status' => 'active']);

        return ['success' => true, 'chunk_count' => count($chunks)];
    }

    public function reprocess(KnowledgeBaseDocument $document): array
    {
        $document->chunks()->delete();

        return $this->process($document);
    }
}
