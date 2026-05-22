import re
import os
import logging
import numpy as np
from fastapi import FastAPI, HTTPException
from pydantic import BaseModel, Field
from sentence_transformers import SentenceTransformer
from transformers import pipeline
from contextlib import asynccontextmanager

logging.basicConfig(level=logging.INFO)
logger = logging.getLogger("ai-service")

# Global models
embedding_model = None
qa_pipeline = None
EMBEDDING_MODEL_NAME = os.getenv("EMBEDDING_MODEL", "intfloat/multilingual-e5-small")
QA_MODEL_NAME = os.getenv("QA_MODEL", "distilbert-base-cased-distilled-squad")
QA_ENABLED = os.getenv("QA_ENABLED", "true").lower() == "true"


@asynccontextmanager
async def lifespan(app: FastAPI):
    """Load models on startup, clean up on shutdown"""
    global embedding_model, qa_pipeline
    logger.info(f"Loading embedding model: {EMBEDDING_MODEL_NAME}")
    embedding_model = SentenceTransformer(EMBEDDING_MODEL_NAME)
    logger.info("Embedding model loaded")

    if QA_ENABLED:
        try:
            logger.info(f"Loading QA model: {QA_MODEL_NAME}")
            qa_pipeline = pipeline("question-answering", model=QA_MODEL_NAME)
            logger.info("QA model loaded")
        except Exception as e:
            logger.warning(f"QA model not loaded: {e}")
            logger.warning("Falling back to sentence-only mode")
    else:
        logger.info("QA is disabled, using sentence-only mode")

    yield

    logger.info("Shutting down AI Service...")


app = FastAPI(title="RAG AI Service", version="2.0.0", lifespan=lifespan)


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


class HealthResponse(BaseModel):
    status: str
    embedding_model: str
    qa_model: str
    qa_enabled: bool


@app.get("/health", response_model=HealthResponse)
def health():
    return HealthResponse(
        status="ok",
        embedding_model=EMBEDDING_MODEL_NAME,
        qa_model=QA_MODEL_NAME,
        qa_enabled=QA_ENABLED,
    )


@app.post("/embed", response_model=EmbedResponse)
def embed(req: EmbedRequest):
    if embedding_model is None:
        raise HTTPException(503, "Embedding model not loaded")
    logger.info(f"Embedding {len(req.texts)} texts")
    vectors = embedding_model.encode(req.texts, normalize_embeddings=True).tolist()
    dim = len(vectors[0]) if vectors else 0
    return EmbedResponse(embeddings=vectors, dimension=dim)


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
    if qa_pipeline is None:
        return None
    try:
        result = qa_pipeline(question=question, context=context)
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
            prefix = "•" if qtype in ('default', 'definisi', 'entitas') else "•"
            lines.append(f"{prefix} **{s['text']}** (relevansi {sim_pct}%)")
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
                'embedding': None,
            })

    if not all_sentences:
        return []

    texts = [s['text'] for s in all_sentences]
    sentence_embs = embedding_model.encode(texts, normalize_embeddings=True)
    q_norm = question_embedding / np.linalg.norm(question_embedding)

    for i, s in enumerate(all_sentences):
        s_emb = sentence_embs[i]
        sim = float(np.dot(q_norm, s_emb))
        sim = max(0, min(1, sim))
        s['similarity'] = round((sim + s['chunk_similarity']) / 2, 4)

    all_sentences.sort(key=lambda x: x['similarity'], reverse=True)
    return all_sentences[:top_k]


@app.post("/answer", response_model=AnswerResponse)
def answer(req: AnswerRequest):
    if embedding_model is None:
        raise HTTPException(503, "Embedding model not loaded")

    logger.info(f"Answering question: {req.question[:80]}... with {len(req.chunks)} chunks")

    if not req.chunks:
        return AnswerResponse(
            answer="Maaf, tidak ada dokumen yang relevan ditemukan.",
            sources=[],
            sentences_used=0,
            mode="no-chunks",
        )

    q_emb = embedding_model.encode([req.question], normalize_embeddings=True)[0]
    top_sentences = _build_sentence_scores(q_emb, req.chunks, req.top_k)

    if not top_sentences:
        return AnswerResponse(
            answer="Maaf, tidak ditemukan kalimat yang relevan dalam dokumen.",
            sources=[],
            sentences_used=0,
            mode="no-sentences",
        )

    if QA_ENABLED and qa_pipeline is not None:
        top_context = " ".join([s['text'] for s in top_sentences[:3]])
        qa_result = _qa_extractive(req.question, top_context)
        if qa_result and qa_result['score'] > 0.3:
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
    """Embed a single text into a vector using the loaded sentence transformer model."""
    if embedding_model is None:
        raise RuntimeError("Embedding model not loaded")

    await ctx.info(f"Embedding text: {text[:50]}...")
    vector = embedding_model.encode([text], normalize_embeddings=True)[0]
    return vector.tolist()


@rag_mcp.tool()
async def rag_embed_texts(
    texts: list[str] = Field(description="List of texts to embed"),
    ctx: Context = None,
) -> list[list[float]]:
    """Embed multiple texts into vectors using the loaded sentence transformer model."""
    if embedding_model is None:
        raise RuntimeError("Embedding model not loaded")

    await ctx.info(f"Embedding {len(texts)} texts")
    vectors = embedding_model.encode(texts, normalize_embeddings=True)
    return vectors.tolist()


@rag_mcp.tool()
async def rag_search(
    question: str = Field(description="Question to search for"),
    chunks: list[dict] = Field(description="List of chunks with 'content' field"),
    top_k: int = Field(default=5, description="Number of top results"),
    ctx: Context = None,
) -> dict:
    """
    Search for relevant sentences in chunks based on a question.
    Returns ranked sentences with similarity scores.
    """
    if embedding_model is None:
        raise RuntimeError("Embedding model not loaded")

    await ctx.report_progress(1, 3, "Embedding question...")
    q_emb = embedding_model.encode([question], normalize_embeddings=True)[0]

    await ctx.report_progress(2, 3, "Scoring sentences...")
    top_sentences = _build_sentence_scores(q_emb, chunks, top_k)

    await ctx.report_progress(3, 3, "Returning results...")
    return {
        "question": question,
        "results": top_sentences,
        "count": len(top_sentences),
    }


@rag_mcp.tool()
async def rag_answer(
    question: str = Field(description="Question to answer"),
    chunks: list[dict] = Field(description="List of chunks with 'content' field"),
    top_k: int = Field(default=5, description="Number of top chunks to use"),
    ctx: Context = None,
) -> dict:
    """
    Answer a question based on provided document chunks.
    Returns formatted answer with sources and mode (qa-extractive or sentence-only).
    """
    if embedding_model is None:
        raise RuntimeError("Embedding model not loaded")

    await ctx.report_progress(1, 4, "Embedding question...")
    q_emb = embedding_model.encode([question], normalize_embeddings=True)[0]

    if not chunks:
        return {
            "answer": "Maaf, tidak ada dokumen yang relevan ditemukan.",
            "sources": [],
            "mode": "no-chunks",
        }

    await ctx.report_progress(2, 4, "Scoring sentences...")
    top_sentences = _build_sentence_scores(q_emb, chunks, top_k)

    if not top_sentences:
        return {
            "answer": "Maaf, tidak ditemukan kalimat yang relevan dalam dokumen.",
            "sources": [],
            "mode": "no-sentences",
        }

    await ctx.report_progress(3, 4, "Generating answer...")

    if QA_ENABLED and qa_pipeline is not None:
        top_context = " ".join([s['text'] for s in top_sentences[:3]])
        qa_result = _qa_extractive(question, top_context)
        if qa_result and qa_result['score'] > 0.3:
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

    answer_text = _format_answer(question, top_sentences)
    mode = "qa-extractive" if any(s.get('is_span') for s in top_sentences) else "sentence-only"

    await ctx.report_progress(4, 4, "Answer generated")
    return {
        "answer": answer_text,
        "sources": sources,
        "mode": mode,
        "sentences_used": len(top_sentences),
    }


# Mount RAG MCP server into FastAPI
rag_mcp_app = rag_mcp.streamable_http_app()
app.mount("/mcp", rag_mcp_app)
logger.info("RAG MCP server mounted at /mcp")


if __name__ == "__main__":
    import uvicorn
    port = int(os.getenv("AI_SERVICE_PORT", "5001"))
    uvicorn.run("main:app", host="0.0.0.0", port=port, reload=False)
