<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StandarMutuRequest;
use App\Models\StandarMutu;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Inertia\Response;

class StandarMutuController extends Controller
{
    /**
     * Display a paginated, filterable list of standar mutu.
     */
    public function index(Request $request): Response|JsonResponse
    {
        $standarMutu = StandarMutu::query()
            ->when($request->search, function ($q, $s) {
                $q->where('kode_standar', 'like', "%{$s}%")
                    ->orWhere('nama_standar', 'like', "%{$s}%")
                    ->orWhere('kategori', 'like', "%{$s}%");
            })
            ->when($request->kategori, function ($q, $s) {
                $q->where('kategori', $s);
            })
            ->when($request->is_active !== null, function ($q) use ($request) {
                $q->where('is_active', filter_var($request->is_active, FILTER_VALIDATE_BOOLEAN));
            })
            ->orderBy('kategori')
            ->orderBy('kode_standar')
            ->paginate((int) $request->per_page ?: 10);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'data' => $standarMutu,
            ]);
        }

        return Inertia::render('Spmi/StandarMutu/Index', [
            'standar_mutu' => $standarMutu,
            'kategori_list' => StandarMutu::select('kategori')->distinct()->pluck('kategori'),
            'filters' => $request->only(['search', 'kategori', 'is_active']),
        ]);
    }

    /**
     * Store a newly created standar mutu.
     */
    public function store(StandarMutuRequest $request)
    {
        $validated = $request->validated();

        DB::transaction(function () use ($validated) {
            StandarMutu::create($validated);
        });

        Log::info('Standar mutu created', [
            'kode_standar' => $validated['kode_standar'],
            'user_id' => auth()->id(),
        ]);

        return redirect()->back()->with('success', 'Standar mutu berhasil ditambahkan.');
    }

    /**
     * Update the specified standar mutu.
     */
    public function update(StandarMutuRequest $request, StandarMutu $standarMutu)
    {
        $validated = $request->validated();

        DB::transaction(function () use ($standarMutu, $validated) {
            $standarMutu->update($validated);
        });

        Log::info('Standar mutu updated', [
            'id' => $standarMutu->id,
            'kode_standar' => $standarMutu->kode_standar,
            'user_id' => auth()->id(),
        ]);

        return redirect()->back()->with('success', 'Standar mutu berhasil diperbarui.');
    }

    /**
     * Remove the specified standar mutu (soft delete).
     */
    public function destroy(StandarMutu $standarMutu)
    {
        // Check no related temuan first
        if ($standarMutu->auditMutus()->exists()) {
            return redirect()->back()->withErrors([
                'standar_mutu' => 'Standar mutu tidak dapat dihapus karena masih memiliki temuan audit terkait.',
            ]);
        }

        if ($standarMutu->edps()->exists()) {
            return redirect()->back()->withErrors([
                'standar_mutu' => 'Standar mutu tidak dapat dihapus karena masih memiliki data EDPS terkait.',
            ]);
        }

        $standarMutu->delete();

        Log::info('Standar mutu deleted', [
            'id' => $standarMutu->id,
            'kode_standar' => $standarMutu->kode_standar,
            'user_id' => auth()->id(),
        ]);

        return redirect()->back()->with('success', 'Standar mutu berhasil dihapus.');
    }
}
