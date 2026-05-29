<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\FasilitasInternetRequest;
use App\Models\FasilitasInternet;
use App\Models\PeriodeAkademik;
use Illuminate\Http\Request;
use Inertia\Inertia;

class FasilitasInternetController extends Controller
{
    public function index(Request $request)
    {
        $items = FasilitasInternet::with('periode')
            ->when($request->periode_id, fn($q, $v) => $q->where('periode_id', $v))
            ->paginate(10);

        return Inertia::render('Kemahasiswaan/FasilitasInternet/Index', [
            'items' => $items,
            'periode_list' => PeriodeAkademik::select('id', 'nama_periode')->get(),
        ]);
    }

    public function store(FasilitasInternetRequest $request)
    {
        $data = $request->validated();
        $data['rasio_mbps_per_mhs'] = $data['bandwidth_total_mbps'] / $data['jumlah_mahasiswa_aktif'];

        FasilitasInternet::create($data);

        return redirect()->back()->with('success', 'Data berhasil ditambahkan.');
    }

    public function update(FasilitasInternetRequest $request, FasilitasInternet $fasilitasInternet)
    {
        $data = $request->validated();
        $data['rasio_mbps_per_mhs'] = $data['bandwidth_total_mbps'] / $data['jumlah_mahasiswa_aktif'];

        $fasilitasInternet->update($data);

        return redirect()->back()->with('success', 'Data berhasil diperbarui.');
    }

    public function destroy(FasilitasInternet $fasilitasInternet)
    {
        $fasilitasInternet->delete();

        return redirect()->back()->with('success', 'Data berhasil dihapus.');
    }
}
