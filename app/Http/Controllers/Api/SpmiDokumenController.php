<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SpmiDokumen;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class SpmiDokumenController extends Controller
{
    /**
     * Display a paginated list of dokumen mutu.
     */
    public function index(Request $request): Response|JsonResponse
    {
        $dokumen = SpmiDokumen::with('approvedBy')
            ->when($request->search, function ($q, $s) {
                $q->where('judul', 'like', "%{$s}%")
                    ->orWhere('nomor_dokumen', 'like', "%{$s}%");
            })
            ->when($request->kategori, function ($q, $s) {
                $q->where('kategori', $s);
            })
            ->when($request->status, function ($q, $s) {
                $q->where('status', $s);
            })
            ->orderBy('created_at', 'DESC')
            ->paginate((int) $request->per_page ?: 10);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'data' => $dokumen,
            ]);
        }

        return Inertia::render('Spmi/DokumenMutu/Index', [
            'dokumen' => $dokumen,
            'kategori_list' => SpmiDokumen::select('kategori')->distinct()->pluck('kategori'),
            'filters' => $request->only(['search', 'kategori', 'status']),
        ]);
    }

    /**
     * Store a newly created dokumen mutu with file upload.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'kategori' => 'required|string|max:100',
            'nomor_dokumen' => 'required|string|max:100',
            'judul' => 'required|string|max:255',
            'deskripsi' => 'nullable|string',
            'file' => 'required|file|mimes:pdf,doc,docx,xlsx|max:20480',
            'version' => 'nullable|integer|min:1',
            'tanggal_berlaku' => 'nullable|date',
            'tanggal_kadaluarsa' => 'nullable|date|after_or_equal:tanggal_berlaku',
            'catatan_revisi' => 'nullable|string',
        ]);

        return DB::transaction(function () use ($request, $validated) {
            // Handle file upload
            if ($request->hasFile('file')) {
                $validated['file_path'] = $request->file('file')->store('dokumen-mutu', 'public');
                $validated['file_original_name'] = $request->file('file')->getClientOriginalName();
            }

            $validated['status'] = $validated['status'] ?? 'draft';
            $validated['version'] = $validated['version'] ?? 1;

            $dokumen = SpmiDokumen::create($validated);

            Log::info('Dokumen mutu created', [
                'dokumen_id' => $dokumen->id,
                'nomor_dokumen' => $dokumen->nomor_dokumen,
                'user_id' => auth()->id(),
            ]);

            return redirect()->back()->with('success', 'Dokumen mutu berhasil ditambahkan.');
        });
    }

    /**
     * Update the specified dokumen mutu.
     */
    public function update(Request $request, SpmiDokumen $spmiDokumen)
    {
        $validated = $request->validate([
            'kategori' => 'sometimes|string|max:100',
            'nomor_dokumen' => 'sometimes|string|max:100',
            'judul' => 'sometimes|string|max:255',
            'deskripsi' => 'nullable|string',
            'file' => 'nullable|file|mimes:pdf,doc,docx,xlsx|max:20480',
            'version' => 'nullable|integer|min:1',
            'tanggal_berlaku' => 'nullable|date',
            'tanggal_kadaluarsa' => 'nullable|date|after_or_equal:tanggal_berlaku',
            'status' => 'nullable|string|in:draft,active,expired,archived',
            'catatan_revisi' => 'nullable|string',
        ]);

        return DB::transaction(function () use ($request, $spmiDokumen, $validated) {
            // Handle file upload
            if ($request->hasFile('file')) {
                // Delete old file
                if ($spmiDokumen->file_path) {
                    Storage::disk('public')->delete($spmiDokumen->file_path);
                }
                $validated['file_path'] = $request->file('file')->store('dokumen-mutu', 'public');
                $validated['file_original_name'] = $request->file('file')->getClientOriginalName();
            }

            // Auto-increment version if marked
            if (isset($validated['version']) && $validated['version'] > $spmiDokumen->version) {
                // Version explicitly set
            }

            $spmiDokumen->update($validated);

            Log::info('Dokumen mutu updated', [
                'dokumen_id' => $spmiDokumen->id,
                'user_id' => auth()->id(),
            ]);

            return redirect()->back()->with('success', 'Dokumen mutu berhasil diperbarui.');
        });
    }

    /**
     * Remove the specified dokumen mutu (soft delete).
     */
    public function destroy(SpmiDokumen $spmiDokumen)
    {
        // Delete associated file
        if ($spmiDokumen->file_path) {
            Storage::disk('public')->delete($spmiDokumen->file_path);
        }

        $spmiDokumen->delete();

        Log::info('Dokumen mutu deleted', [
            'dokumen_id' => $spmiDokumen->id,
            'user_id' => auth()->id(),
        ]);

        return redirect()->back()->with('success', 'Dokumen mutu berhasil dihapus.');
    }
}
