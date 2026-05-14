<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AuditMutu;
use App\Models\Prodi;
use App\Models\PeriodeAkademik;
use Illuminate\Http\Request;
use Inertia\Inertia;

class AuditMutuController extends Controller
{
    public function index(Request $request)
    {
        $audit = AuditMutu::with('prodi', 'periode')
            ->when($request->search, function ($q, $s) {
                $q->where('judul_audit', 'like', "%{$s}%")
                  ->orWhere('auditor', 'like', "%{$s}%");
            })
            ->when($request->status, function ($q, $s) {
                $q->where('status', $s);
            })
            ->paginate(10);

        return Inertia::render('Spmi/Audit/Index', [
            'audit' => $audit,
            'prodi_list' => Prodi::select('id', 'nama_prodi')->get(),
            'periode_list' => PeriodeAkademik::select('id', 'nama_periode')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'prodi_id' => 'required|exists:m_prodi,id',
            'periode_id' => 'required|exists:m_periode_akademik,id',
            'judul_audit' => 'required|string',
            'tanggal_audit' => 'required|date',
            'auditor' => 'nullable|string',
            'temuan' => 'nullable|string',
            'rekomendasi' => 'nullable|string',
        ]);

        AuditMutu::create($validated);

        return redirect()->back()->with('success', 'Audit mutu berhasil ditambahkan.');
    }

    public function update(Request $request, AuditMutu $auditMutu)
    {
        $validated = $request->validate([
            'prodi_id' => 'required|exists:m_prodi,id',
            'periode_id' => 'required|exists:m_periode_akademik,id',
            'judul_audit' => 'required|string',
            'tanggal_audit' => 'required|date',
            'auditor' => 'nullable|string',
            'temuan' => 'nullable|string',
            'rekomendasi' => 'nullable|string',
            'tindak_lanjut' => 'nullable|string',
            'status' => 'required|string|in:open,in_progress,closed',
        ]);

        $auditMutu->update($validated);

        return redirect()->back()->with('success', 'Audit mutu berhasil diperbarui.');
    }

    public function destroy(AuditMutu $auditMutu)
    {
        $auditMutu->delete();
        return redirect()->back()->with('success', 'Audit mutu berhasil dihapus.');
    }
}
