"""
PDF MCP Tools for Akreditasi System
Provides PDF extraction, table extraction, and OCR capabilities
"""
import os
import logging
from typing import Optional
from pathlib import Path

from mcp.server.fastmcp import FastMCP, Context
from pydantic import Field

logger = logging.getLogger("mcp.pdf")

pdf_mcp = FastMCP(
    name="akreditasi-pdf",
    stateless_http=True,
    json_response=True,
)


def _extract_text_smalot(file_path: str) -> str:
    """Extract text using Smalot PDF parser"""
    try:
        from pdfplumber import open as pdf_open
        text = ""
        with pdf_open(file_path) as pdf:
            for page in pdf.pages:
                page_text = page.extract_text()
                if page_text:
                    text += page_text + "\n"
        return text
    except ImportError:
        return _extract_text_cli(file_path)


def _extract_text_cli(file_path: str) -> str:
    """Extract text using pdftotext CLI"""
    import subprocess
    try:
        result = subprocess.run(
            ["pdftotext", "-layout", file_path, "-"],
            capture_output=True, text=True, timeout=30
        )
        return result.stdout
    except (FileNotFoundError, subprocess.TimeoutExpired):
        return ""


def _count_pages(file_path: str) -> int:
    """Count pages in PDF"""
    try:
        from pdfplumber import open as pdf_open
        with pdf_open(file_path) as pdf:
            return len(pdf.pages)
    except ImportError:
        import subprocess
        try:
            result = subprocess.run(
                ["pdfinfo", file_path],
                capture_output=True, text=True, timeout=10
            )
            for line in result.stdout.split("\n"):
                if line.startswith("Pages:"):
                    return int(line.split(":")[1].strip())
        except Exception:
            pass
    return 0


@pdf_mcp.tool()
async def pdf_extract_text(
    file_path: str = Field(description="Path to PDF file"),
    ctx: Context = None,
) -> dict:
    """
    Extract text content from a PDF file.
    Returns text, page count, and extraction status.
    """
    if not os.path.exists(file_path):
        return {"error": f"File not found: {file_path}"}

    await ctx.info(f"Extracting text from: {file_path}")

    text = _extract_text_smalot(file_path)
    page_count = _count_pages(file_path)

    return {
        "file_path": file_path,
        "text": text,
        "page_count": page_count,
        "char_count": len(text),
        "status": "success" if text else "warning",
    }


@pdf_mcp.tool()
async def pdf_extract_tables(
    file_path: str = Field(description="Path to PDF file"),
    ctx: Context = None,
) -> dict:
    """
    Extract tables from a PDF file.
    Returns list of tables with rows and columns.
    """
    if not os.path.exists(file_path):
        return {"error": f"File not found: {file_path}"}

    await ctx.info(f"Extracting tables from: {file_path}")

    try:
        from pdfplumber import open as pdf_open
        tables = []
        with pdf_open(file_path) as pdf:
            for i, page in enumerate(pdf.pages):
                page_tables = page.extract_tables()
                for j, table in enumerate(page_tables):
                    tables.append({
                        "page": i + 1,
                        "table_index": j,
                        "rows": len(table),
                        "columns": len(table[0]) if table else 0,
                        "data": table,
                    })

        return {
            "file_path": file_path,
            "table_count": len(tables),
            "tables": tables,
            "status": "success",
        }
    except ImportError:
        return {
            "error": "pdfplumber not installed. Install with: pip install pdfplumber",
            "status": "error",
        }


@pdf_mcp.tool()
async def pdf_get_metadata(
    file_path: str = Field(description="Path to PDF file"),
    ctx: Context = None,
) -> dict:
    """
    Get metadata from a PDF file.
    Returns author, title, creation date, page count, etc.
    """
    if not os.path.exists(file_path):
        return {"error": f"File not found: {file_path}"}

    await ctx.info(f"Getting metadata from: {file_path}")

    file_size = os.path.getsize(file_path)

    try:
        from pdfplumber import open as pdf_open
        with pdf_open(file_path) as pdf:
            metadata = pdf.metadata or {}
            return {
                "file_path": file_path,
                "file_size": file_size,
                "page_count": len(pdf.pages),
                "title": metadata.get("Title", ""),
                "author": metadata.get("Author", ""),
                "creator": metadata.get("Creator", ""),
                "producer": metadata.get("Producer", ""),
                "creation_date": metadata.get("CreationDate", ""),
                "status": "success",
            }
    except ImportError:
        return {
            "file_path": file_path,
            "file_size": file_size,
            "page_count": _count_pages(file_path),
            "status": "partial",
            "note": "Install pdfplumber for full metadata",
        }
