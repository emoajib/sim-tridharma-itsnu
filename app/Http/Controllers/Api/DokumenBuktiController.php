<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DokumenBukti;
use App\Models\Dosen;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class DokumenBuktiController extends Controller
{
    public function index(Request $request)
    {
        $dokumen = DokumenBukti::with(['dosen', 'prodi'])
            ->when($request->search, function ($query, $search) {
                $query->where('nama_dokumen', 'like', "%{$search}%");
            })
            ->paginate(10);

        return Inertia::render('Dokumen/Index', [
            'dokumen' => $dokumen,
            'dosen_list' => Dosen::select('id', 'nama_depan', 'nidn')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'dosen_id' => 'nullable|exists:m_dosen,id',
            'prodi_id' => 'nullable|exists:m_prodi,id',
            'nama_dokumen' => 'required|string',
            'file' => 'required|file|mimes:pdf,jpg,jpeg,png,doc,docx|max:10240',
            'keterangan' => 'nullable|string',
        ]);

        $file = $request->file('file');
        $filePath = $file->store('dokumen', 'public');
        $validated['file_path'] = $filePath;
        $validated['file_type'] = $file->getClientOriginalExtension();
        $validated['file_size'] = $file->getSize();
        $validated['hash'] = md5_file($file->getRealPath());

        DokumenBukti::create($validated);

        return redirect()->back()->with('success', 'Dokumen berhasil ditambahkan.');
    }

    public function update(Request $request, DokumenBukti $dokumenBukti)
    {
        $validated = $request->validate([
            'dosen_id' => 'nullable|exists:m_dosen,id',
            'prodi_id' => 'nullable|exists:m_prodi,id',
            'nama_dokumen' => 'required|string',
            'file' => 'nullable|file|mimes:pdf,jpg,jpeg,png,doc,docx|max:10240',
            'keterangan' => 'nullable|string',
        ]);

        if ($request->hasFile('file')) {
            Storage::disk('public')->delete($dokumenBukti->file_path);

            $file = $request->file('file');
            $filePath = $file->store('dokumen', 'public');
            $validated['file_path'] = $filePath;
            $validated['file_type'] = $file->getClientOriginalExtension();
            $validated['file_size'] = $file->getSize();
            $validated['hash'] = md5_file($file->getRealPath());
        }

        $dokumenBukti->update($validated);

        return redirect()->back()->with('success', 'Dokumen berhasil diperbarui.');
    }

    public function destroy(DokumenBukti $dokumenBukti)
    {
        Storage::disk('public')->delete($dokumenBukti->file_path);

        $dokumenBukti->delete();

        return redirect()->back()->with('success', 'Dokumen berhasil dihapus.');
    }
}
