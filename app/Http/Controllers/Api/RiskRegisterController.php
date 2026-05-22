<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\RiskRegisterRequest;
use App\Models\PeriodeAkademik;
use App\Models\Prodi;
use App\Models\RiskRegister;
use Illuminate\Http\Request;
use Inertia\Inertia;

class RiskRegisterController extends Controller
{
    public function index(Request $request)
    {
        $risks = RiskRegister::with('prodi', 'periode')
            ->when($request->search, function ($q, $s) {
                $q->where('nama_risiko', 'like', "%{$s}%")
                    ->orWhere('kategori', 'like', "%{$s}%");
            })
            ->when($request->status, function ($q, $s) {
                $q->where('status', $s);
            })
            ->paginate(10);

        return Inertia::render('Spmi/Risk/Index', [
            'risks' => $risks,
            'prodi_list' => Prodi::select('id', 'nama_prodi')->get(),
            'periode_list' => PeriodeAkademik::select('id', 'nama_periode')->get(),
        ]);
    }

    public function store(RiskRegisterRequest $request)
    {
        $data = $request->validated();
        $data['skor_risiko'] = $this->calculateSkor($data['dampak'], $data['probabilitas']);

        RiskRegister::create($data);

        return redirect()->back()->with('success', 'Risk register berhasil ditambahkan.');
    }

    public function update(RiskRegisterRequest $request, RiskRegister $riskRegister)
    {
        $data = $request->validated();
        $data['skor_risiko'] = $this->calculateSkor($data['dampak'], $data['probabilitas']);

        $riskRegister->update($data);

        return redirect()->back()->with('success', 'Risk register berhasil diperbarui.');
    }

    public function destroy(RiskRegister $riskRegister)
    {
        $riskRegister->delete();

        return redirect()->back()->with('success', 'Risk register berhasil dihapus.');
    }

    private function calculateSkor(string $dampak, string $probabilitas): string
    {
        $map = ['rendah' => 1, 'sedang' => 2, 'tinggi' => 3];
        $skor = ($map[$dampak] ?? 1) * ($map[$probabilitas] ?? 1);

        return match (true) {
            $skor >= 6 => 'tinggi',
            $skor >= 3 => 'sedang',
            default => 'rendah',
        };
    }
}
