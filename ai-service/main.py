# Vetted by AI - Manual Review Required by Senior Engineer/Manager
import re
import os
import logging
import gc
import torch
import numpy as np
from fastapi import FastAPI, HTTPException
from pydantic import BaseModel, Field
from sentence_transformers import SentenceTransformer
from transformers import pipeline
from contextlib import asynccontextmanager

# Configure logging
logging.basicConfig(
    level=logging.INFO,
    format="%(asctime)s [%(name)s] %(levelname)s: %(message)s"
)
logger = logging.getLogger("ai-service")

# Optimization: Limit torch threads to prevent CPU exhaustion
TORCH_THREADS = int(os.getenv("TORCH_THREADS", "2"))
torch.set_num_threads(TORCH_THREADS)
logger.info(f"Torch threads limited to {TORCH_THREADS}")

class ModelManager:
    """Singleton manager for AI models to ensure stability and resource control."""
    _instance = None
    
    def __init__(self):
        self.embedding_model = None
        self.qa_pipeline = None
        self.embedding_model_name = os.getenv("EMBEDDING_MODEL", "intfloat/multilingual-e5-small")
        self.qa_model_name = os.getenv("QA_MODEL", "distilbert-base-cased-distilled-squad")
        self.qa_enabled = os.getenv("QA_ENABLED", "true").lower() == "true"
        self.is_ready = False

    @classmethod
    def get_instance(cls):
        if cls._instance is None:
            cls._instance = cls()
        return cls._instance

    def load_models(self):
        try:
            logger.info(f"Loading embedding model: {self.embedding_model_name}")
            self.embedding_model = SentenceTransformer(self.embedding_model_name)
            logger.info("Embedding model loaded successfully")

            if self.qa_enabled:
                logger.info(f"Loading QA model: {self.qa_model_name}")
                # Use CPU for stability unless GPU is explicitly requested and available
                device = -1 # CPU
                self.qa_pipeline = pipeline("question-answering", model=self.qa_model_name, device=device)
                logger.info("QA model loaded successfully")
            
            self.is_ready = True
        except Exception as e:
            logger.error(f"Failed to load models: {e}")
            self.is_ready = False
            raise e

    def unload_models(self):
        logger.info("Unloading models and clearing memory...")
        self.embedding_model = None
        self.qa_pipeline = None
        self.is_ready = False
        gc.collect()
        if torch.cuda.is_available():
            torch.cuda.empty_cache()
        logger.info("Memory cleared")

model_manager = ModelManager.get_instance()

@asynccontextmanager
async def lifespan(app: FastAPI):
    """Lifecycle management for the FastAPI application."""
    model_manager.load_models()
    yield
    model_manager.unload_models()

app = FastAPI(
    title="RAG AI Service - Akreditasi",
    version="2.1.0",
    lifespan=lifespan
)

# Requests/Response Models
class EmbedRequest(BaseModel):
    texts: list[str]

class EmbedResponse(BaseModel):
    embeddings: list[list[float]]
    dimension: int

class AnswerRequest(BaseModel):
    question: str
    chunks: list[dict]
    top_k: int = 5

class AnswerResponse(BaseModel):
    answer: str
    sources: list[dict]
    sentences_used: int
    mode: str

@app.get("/health")
def health():
    return {
        "status": "ok" if model_manager.is_ready else "initializing",
        "ready": model_manager.is_ready
    }

@app.get("/status")
def status():
    return {
        "status": "ok" if model_manager.is_ready else "error",
        "embedding_model": model_manager.embedding_model_name,
        "qa_enabled": model_manager.qa_enabled,
        "qa_model": model_manager.qa_model_name if model_manager.qa_enabled else None,
        "torch_threads": TORCH_THREADS,
        "device": str(torch.get_num_threads())
    }

@app.post("/embed", response_model=EmbedResponse)
def embed(req: EmbedRequest):
    if not model_manager.is_ready:
        raise HTTPException(503, "Models are not ready")
    
    try:
        vectors = model_manager.embedding_model.encode(req.texts, normalize_embeddings=True).tolist()
        dim = len(vectors[0]) if vectors else 0
        return EmbedResponse(embeddings=vectors, dimension=dim)
    except Exception as e:
        logger.error(f"Embedding error: {e}")
        raise HTTPException(500, str(e))

def _split_sentences(text: str) -> list[str]:
    sentences = re.split(r'(?<=[.!?])\s+', text)
    return [s.strip() for s in sentences if len(s.strip()) > 10]

def _classify_question(question: str) -> str:
    q = question.lower().strip()
    if re.search(r'\b(berapa|jumlah|total|skor|rata-rata|minimal|maksimal)\b', q):
        return 'numerik'
    if re.search(r'\b(apa itu|definisi|pengertian|makna|maksud)\b', q):
        return 'definisi'
    if re.search(r'\b(bagaimana|cara|langkah|prosedur|proses|tahap|alur|metode)\b', q):
        return 'prosedur'
    if re.search(r'\b(siapa|nama|dosen|ketua|dekan|rektor|kepala)\b', q):
        return 'entitas'
    if re.search(r'\b(kenapa|mengapa|sebab|alasan|tujuan|manfaat)\b', q):
        return 'alasan'
    if re.search(r'\b(kapan|tanggal|waktu|periode|tahun|bulan)\b', q):
        return 'waktu'
    if re.search(r'\b(di mana|lokasi|tempat|alamat)\b', q):
        return 'lokasi'
    return 'default'

def _qa_extractive(question: str, context: str) -> dict | None:
    if not model_manager.qa_pipeline:
        return None
    try:
        result = model_manager.qa_pipeline(question=question, context=context)
        if result['score'] > 0.3:
            return {
                'answer': result['answer'],
                'score': round(result['score'], 4),
                'start': result['start'],
                'end': result['end'],
            }
    except Exception as e:
        logger.warning(f"QA extraction failed: {e}")
    return None

def _format_answer(question: str, top_sentences: list[dict]) -> str:
    qtype = _classify_question(question)
    sources_seen = set()
    source_lines = []

    lines = []
    for i, s in enumerate(top_sentences):
        doc_label = s.get('document_judul', 'Dokumen')
        source_key = f"{doc_label} ({s.get('document_sumber', '')})"
        if source_key not in sources_seen:
            sources_seen.add(source_key)
            source_lines.append(f"- {source_key}")

        sim_pct = round(s['similarity'] * 100, 1)
        if s.get('is_span'):
            lines.append(f"• **{s['text']}** (relevansi {sim_pct}%)")
        else:
            lines.append(f"• {s['text']} (relevansi {sim_pct}%)")

    if not lines:
        return "Maaf, tidak ditemukan kalimat yang relevan dalam dokumen."

    header_map = {
        'numerik': "Berdasarkan data dokumen:\n",
        'definisi': "Dari dokumen yang tersedia:\n",
        'prosedur': "Langkah-langkah menurut dokumen:\n",
        'entitas': "Informasi dari dokumen:\n",
        'alasan': "Berdasarkan dokumen:\n",
        'waktu': "Informasi waktu dari dokumen:\n",
        'lokasi': "Lokasi menurut dokumen:\n",
        'default': "Informasi dari dokumen:\n",
    }

    header = header_map.get(qtype, "Informasi dari dokumen:\n")
    answer = header + "\n" + "\n".join(lines)

    if source_lines:
        answer += "\n\nSumber:\n" + "\n".join(source_lines[:3])

    return answer

def _build_sentence_scores(question_embedding: np.ndarray, chunks: list[dict], top_k: int) -> list[dict]:
    all_sentences = []
    for chunk in chunks:
        raw_text = chunk.get('content', '')
        if not raw_text:
            continue
        sentences = _split_sentences(raw_text)
        for st in sentences:
            all_sentences.append({
                'text': st,
                'document_judul': chunk.get('document_judul', 'Dokumen'),
                'document_sumber': chunk.get('document_sumber', ''),
                'chunk_similarity': chunk.get('similarity', 0),
                'similarity': 0.0
            })

    if not all_sentences:
        return []

    texts = [s['text'] for s in all_sentences]
    sentence_embs = model_manager.embedding_model.encode(texts, normalize_embeddings=True)
    q_norm = question_embedding / np.linalg.norm(question_embedding)

    for i, s in enumerate(all_sentences):
        s_emb = sentence_embs[i]
        sim = float(np.dot(q_norm, s_emb))
        sim = max(0, min(1, sim))
        # Weighted similarity: 70% sentence relevance, 30% chunk relevance
        s['similarity'] = round((sim * 0.7) + (s['chunk_similarity'] * 0.3), 4)

    all_sentences.sort(key=lambda x: x['similarity'], reverse=True)
    return all_sentences[:top_k]

@app.post("/answer", response_model=AnswerResponse)
def answer(req: AnswerRequest):
    if not model_manager.is_ready:
        raise HTTPException(503, "Models are not ready")

    if not req.chunks:
        return AnswerResponse(
            answer="Maaf, tidak ada dokumen yang relevan ditemukan.",
            sources=[],
            sentences_used=0,
            mode="no-chunks",
        )

    try:
        q_emb = model_manager.embedding_model.encode([req.question], normalize_embeddings=True)[0]
        top_sentences = _build_sentence_scores(q_emb, req.chunks, req.top_k)

        if not top_sentences:
            return AnswerResponse(
                answer="Maaf, tidak ditemukan kalimat yang relevan dalam dokumen.",
                sources=[],
                sentences_used=0,
                mode="no-sentences",
            )

        if model_manager.qa_pipeline:
            top_context = " ".join([s['text'] for s in top_sentences[:3]])
            qa_result = _qa_extractive(req.question, top_context)
            if qa_result and qa_result['score'] > 0.4:
                top_sentences[0]['text'] = qa_result['answer']
                top_sentences[0]['is_span'] = True

        sources = []
        seen_sources = set()
        for s in top_sentences:
            key = f"{s['document_judul']}|{s['document_sumber']}"
            if key not in seen_sources:
                seen_sources.add(key)
                sources.append({
                    'judul': s['document_judul'],
                    'sumber': s['document_sumber'],
                    'skor': round(s.get('chunk_similarity', 0) * 100, 1),
                })

        answer_text = _format_answer(req.question, top_sentences)
        mode = "qa-extractive" if any(s.get('is_span') for s in top_sentences) else "sentence-only"

        return AnswerResponse(
            answer=answer_text,
            sources=sources,
            sentences_used=len(top_sentences),
            mode=mode,
        )
    except Exception as e:
        logger.error(f"Answer processing error: {e}")
        raise HTTPException(500, str(e))

# =============================================================================
# MCP Server Integration
# =============================================================================
from mcp.server.fastmcp import FastMCP, Context

rag_mcp = FastMCP(
    name="akreditasi-rag",
    stateless_http=True,
    json_response=True,
)

@rag_mcp.tool()
async def rag_embed_text(
    text: str = Field(description="Text to embed"),
    ctx: Context = None,
) -> list[float]:
    """Embed a single text into a vector."""
    if not model_manager.is_ready:
        raise RuntimeError("Models not ready")
    
    vector = model_manager.embedding_model.encode([text], normalize_embeddings=True)[0]
    return vector.tolist()

@rag_mcp.tool()
async def rag_answer(
    question: str = Field(description="Question to answer"),
    chunks: list[dict] = Field(description="List of chunks with 'content' field"),
    top_k: int = Field(default=5, description="Number of top chunks to use"),
    ctx: Context = None,
) -> dict:
    """Answer a question based on document chunks using RAG logic."""
    if not model_manager.is_ready:
        raise RuntimeError("Models not ready")

    q_emb = model_manager.embedding_model.encode([question], normalize_embeddings=True)[0]
    top_sentences = _build_sentence_scores(q_emb, chunks, top_k)

    if not top_sentences:
        return {"answer": "Maaf, data tidak ditemukan.", "sources": [], "mode": "none"}

    answer_text = _format_answer(question, top_sentences)
    return {"answer": answer_text, "count": len(top_sentences)}

# Mount RAG MCP server
app.mount("/mcp", rag_mcp.streamable_http_app())

if __name__ == "__main__":
    import uvicorn
    port = int(os.getenv("AI_SERVICE_PORT", "5001"))
    uvicorn.run("main:app", host="0.0.0.0", port=port, reload=False, workers=1)

