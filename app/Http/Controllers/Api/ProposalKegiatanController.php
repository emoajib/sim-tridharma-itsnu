<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ProposalKegiatanRequest;
use App\Models\ProposalKegiatan;
use App\Models\Ormawa;
use App\Models\Prodi;
use App\Models\PeriodeAkademik;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ProposalKegiatanController extends Controller
{
    public function index(Request $request)
    {
        $items = ProposalKegiatan::with(['ormawa', 'prodi', 'periode'])
            ->when($request->search, fn($q, $s) => $q->where('judul_kegiatan', 'like', "%{$s}%"))
            ->when($request->status_kegiatan, fn($q, $v) => $q->where('status_kegiatan', $v))
            ->paginate(10);

        return Inertia::render('Kemahasiswaan/ProposalKegiatan/Index', [
            'items' => $items,
            'ormawa_list' => Ormawa::select('id', 'nama')->get(),
            'prodi_list' => Prodi::select('id', 'nama_prodi')->get(),
            'periode_list' => PeriodeAkademik::select('id', 'nama_periode')->get(),
        ]);
    }

    public function store(ProposalKegiatanRequest $request)
    {
        ProposalKegiatan::create($request->validated());

        return redirect()->back()->with('success', 'Data berhasil ditambahkan.');
    }

    public function update(ProposalKegiatanRequest $request, ProposalKegiatan $proposalKegiatan)
    {
        $proposalKegiatan->update($request->validated());

        return redirect()->back()->with('success', 'Data berhasil diperbarui.');
    }

    public function destroy(ProposalKegiatan $proposalKegiatan)
    {
        $proposalKegiatan->delete();

        return redirect()->back()->with('success', 'Data berhasil dihapus.');
    }

    public function submit(ProposalKegiatan $proposalKegiatan)
    {
        $next = $proposalKegiatan->jenis_proposal === 'HIMA' ? 'Review_Kaprodi' : 'Review_Pembina';
        $proposalKegiatan->update(['status_kegiatan' => $next]);

        return redirect()->back()->with('success', 'Proposal berhasil dikirim.');
    }

    public function approvePembina(ProposalKegiatan $proposalKegiatan)
    {
        $proposalKegiatan->update(['status_kegiatan' => 'Review_Fakultas']);

        return redirect()->back()->with('success', 'Proposal telah disetujui Pembina.');
    }

    public function approveFakultas(ProposalKegiatan $proposalKegiatan)
    {
        $proposalKegiatan->update(['status_kegiatan' => 'Review_WR3']);

        return redirect()->back()->with('success', 'Proposal telah disetujui Fakultas.');
    }

    public function approveWR3(Request $request, ProposalKegiatan $proposalKegiatan)
    {
        $request->validate(['rab_disetujui' => 'required|numeric|min:0']);

        $proposalKegiatan->update([
            'status_kegiatan' => 'Approved',
            'rab_disetujui' => $request->rab_disetujui,
        ]);

        return redirect()->back()->with('success', 'Proposal telah disetujui WR3.');
    }

    public function reject(ProposalKegiatan $proposalKegiatan)
    {
        $proposalKegiatan->update(['status_kegiatan' => 'Rejected']);

        return redirect()->back()->with('success', 'Proposal telah ditolak.');
    }

    public function submitLPJ(Request $request, ProposalKegiatan $proposalKegiatan)
    {
        $request->validate(['file_lpj' => 'required|file|mimes:pdf|max:5120']);

        $proposalKegiatan->update([
            'file_lpj' => $request->file('file_lpj')->store('lpj', 'public'),
            'status_kegiatan' => 'LPJ_Submitted',
        ]);

        return redirect()->back()->with('success', 'LPJ berhasil dikirim.');
    }

    public function approveLPJ(ProposalKegiatan $proposalKegiatan)
    {
        $proposalKegiatan->update(['status_kegiatan' => 'LPJ_Approved']);

        return redirect()->back()->with('success', 'LPJ telah disetujui.');
    }

    public function approveKaprodi(ProposalKegiatan $proposalKegiatan)
    {
        $proposalKegiatan->update(['status_kegiatan' => 'Review_Dekan']);

        return redirect()->back()->with('success', 'Proposal telah disetujui Kaprodi.');
    }

    public function approveDekan(Request $request, ProposalKegiatan $proposalKegiatan)
    {
        $request->validate(['rab_disetujui' => 'required|numeric|min:0']);

        $proposalKegiatan->update([
            'status_kegiatan' => 'Approved',
            'rab_disetujui' => $request->rab_disetujui,
            'status_hima' => 'Approved',
        ]);

        return redirect()->back()->with('success', 'Proposal telah disetujui Dekan.');
    }
}
