<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\DokumenBuktiRequest;
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

    public function store(DokumenBuktiRequest $request)
    {
        $data = $request->validated();

        $file = $request->file('file');
        $filePath = $file->store('dokumen', 'public');
        $data['file_path'] = $filePath;
        $data['file_type'] = $file->getClientOriginalExtension();
        $data['file_size'] = $file->getSize();
        $data['hash'] = md5_file($file->getRealPath());

        DokumenBukti::create($data);

        return redirect()->back()->with('success', 'Dokumen berhasil ditambahkan.');
    }

    public function update(DokumenBuktiRequest $request, DokumenBukti $dokumenBukti)
    {
        $data = $request->validated();

        if ($request->hasFile('file')) {
            Storage::disk('public')->delete($dokumenBukti->file_path);

            $file = $request->file('file');
            $filePath = $file->store('dokumen', 'public');
            $data['file_path'] = $filePath;
            $data['file_type'] = $file->getClientOriginalExtension();
            $data['file_size'] = $file->getSize();
            $data['hash'] = md5_file($file->getRealPath());
        }

        $dokumenBukti->update($data);

        return redirect()->back()->with('success', 'Dokumen berhasil diperbarui.');
    }

    public function destroy(DokumenBukti $dokumenBukti)
    {
        Storage::disk('public')->delete($dokumenBukti->file_path);

        $dokumenBukti->delete();

        return redirect()->back()->with('success', 'Dokumen berhasil dihapus.');
    }
}
