<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Cpl;
use App\Models\MataKuliah;
use App\Models\Prodi;
use App\Models\Kurikulum;
use Illuminate\Http\Request;
use Inertia\Inertia;

class KurikulumMappingController extends Controller
{
    public function index(Request $request)
    {
        $prodiId = $request->get('prodi_id');
        $kurikulumId = $request->get('kurikulum_id');

        $prodi_list = Prodi::select('id', 'nama_prodi')->get();
        $kurikulum_list = Kurikulum::select('id', 'nama_kurikulum', 'prodi_id')->get();

        $cpls = collect();
        $mks = collect();
        $mapping = collect();

        if ($prodiId) {
            $cpls = Cpl::with(['prodi'])->where('prodi_id', $prodiId)->get();
            $mks = MataKuliah::where('prodi_id', $prodiId)->get();

            if ($kurikulumId) {
                $mks = MataKuliah::whereHas('kurikulums', fn($q) => $q->where('m_kurikulum.id', $kurikulumId))
                    ->orWhere('prodi_id', $prodiId)
                    ->get();
            }

            $mapping = \DB::table('m_cpl_mk')
                ->whereIn('cpl_id', $cpls->pluck('id'))
                ->whereIn('mata_kuliah_id', $mks->pluck('id'))
                ->get();
        }

        return Inertia::render('Kurikulum/Mapping/Index', [
            'cpls' => $cpls,
            'mata_kuliahs' => $mks,
            'mapping' => $mapping,
            'prodi_list' => $prodi_list,
            'kurikulum_list' => $kurikulum_list,
            'selectedProdi' => $prodiId ? (int) $prodiId : null,
            'selectedKurikulum' => $kurikulumId ? (int) $kurikulumId : null,
        ]);
    }

    public function toggleMapping(Request $request)
    {
        $validated = $request->validate([
            'cpl_id' => 'required|exists:m_cpl,id',
            'mata_kuliah_id' => 'required|exists:m_mata_kuliah,id',
            'tingkat' => 'nullable|string',
        ]);

        $exists = \DB::table('m_cpl_mk')
            ->where('cpl_id', $validated['cpl_id'])
            ->where('mata_kuliah_id', $validated['mata_kuliah_id'])
            ->exists();

        if ($exists) {
            \DB::table('m_cpl_mk')
                ->where('cpl_id', $validated['cpl_id'])
                ->where('mata_kuliah_id', $validated['mata_kuliah_id'])
                ->delete();
            $message = 'Mapping dihapus';
        } else {
            \DB::table('m_cpl_mk')->insert([
                'cpl_id' => $validated['cpl_id'],
                'mata_kuliah_id' => $validated['mata_kuliah_id'],
                'tingkat' => $validated['tingkat'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            $message = 'Mapping ditambahkan';
        }

        return redirect()->back()->with('success', $message);
    }
}
