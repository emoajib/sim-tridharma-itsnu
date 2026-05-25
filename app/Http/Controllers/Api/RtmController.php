<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\RtmRequest;
use App\Models\Rtm;
use App\Models\RtmActionItem;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class RtmController extends Controller
{
    /**
     * Display a paginated list of RTM.
     */
    public function index(Request $request): Response|JsonResponse
    {
        $rtm = Rtm::with(['dipimpinOleh', 'actionItems.picUser'])
            ->when($request->search, function ($q, $s) {
                $q->where('judul', 'like', "%{$s}%")
                    ->orWhere('agenda', 'like', "%{$s}%");
            })
            ->when($request->tanggal_from, function ($q, $s) {
                $q->whereDate('tanggal_rapat', '>=', $s);
            })
            ->when($request->tanggal_to, function ($q, $s) {
                $q->whereDate('tanggal_rapat', '<=', $s);
            })
            ->orderBy('tanggal_rapat', 'DESC')
            ->paginate((int) $request->per_page ?: 10);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'data' => $rtm,
            ]);
        }

        return Inertia::render('Spmi/Rtm/Index', [
            'rtm' => $rtm,
            'filters' => $request->only(['search', 'tanggal_from', 'tanggal_to']),
        ]);
    }

    /**
     * Store a newly created RTM with action items.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'judul' => 'required|string|max:255',
            'tanggal_rapat' => 'required|date',
            'agenda' => 'nullable|string',
            'notulen' => 'nullable|string',
            'file_notulen' => 'nullable|file|mimes:pdf,doc,docx|max:10240',
            'dipimpin_oleh' => 'nullable|exists:users,id',
            'action_items' => 'nullable|array',
            'action_items.*.deskripsi' => 'required|string|max:500',
            'action_items.*.pic_user_id' => 'required|exists:users,id',
            'action_items.*.deadline' => 'required|date',
        ]);

        return DB::transaction(function () use ($request, $validated) {
            // Handle file upload
            if ($request->hasFile('file_notulen')) {
                $validated['file_notulen'] = $request->file('file_notulen')
                    ->store('rtm-notulen', 'public');
            }

            $actionItems = $validated['action_items'] ?? [];
            unset($validated['action_items']);

            $rtm = Rtm::create($validated);

            // Create action items
            foreach ($actionItems as $item) {
                RtmActionItem::create([
                    'rtm_id' => $rtm->id,
                    'deskripsi' => $item['deskripsi'],
                    'pic_user_id' => $item['pic_user_id'],
                    'deadline' => $item['deadline'],
                    'status' => 'open',
                ]);
            }

            Log::info('RTM created', [
                'rtm_id' => $rtm->id,
                'judul' => $rtm->judul,
                'action_items_count' => count($actionItems),
                'user_id' => auth()->id(),
            ]);

            return redirect()->back()->with('success', 'Rapat tinjauan mutu berhasil dicatat.');
        });
    }

    /**
     * Display the specified RTM with action items.
     */
    public function show(Rtm $rtm): Response|JsonResponse
    {
        $rtm->load(['dipimpinOleh', 'actionItems.picUser']);

        if (request()->expectsJson()) {
            return response()->json([
                'success' => true,
                'data' => $rtm,
            ]);
        }

        return Inertia::render('Spmi/Rtm/Show', [
            'rtm' => $rtm,
        ]);
    }

    public function update(RtmRequest $request, Rtm $rtm)
    {
        $rtm->update($request->validated());
        return redirect()->route('spmi.rtm')->with('success', 'RTM berhasil diperbarui.');
    }

    public function destroy(Rtm $rtm)
    {
        $rtm->delete();
        return redirect()->route('spmi.rtm')->with('success', 'RTM berhasil dihapus.');
    }

    public function storeActionItem(Request $request, Rtm $rtm)
    {
        $validated = $request->validate([
            'deskripsi' => 'required|string|max:1000',
            'pic_user_id' => 'nullable|exists:users,id',
            'deadline' => 'nullable|date',
        ]);
        $item = $rtm->actionItems()->create($validated);
        return redirect()->route('spmi.rtm.show', $rtm)->with('success', 'Action item berhasil ditambahkan.');
    }

    public function updateActionItem(Request $request, Rtm $rtm, RtmActionItem $rtmActionItem)
    {
        $validated = $request->validate([
            'deskripsi' => 'required|string|max:1000',
            'pic_user_id' => 'nullable|exists:users,id',
            'deadline' => 'nullable|date',
            'hasil' => 'nullable|string',
        ]);
        $rtmActionItem->update($validated);
        return redirect()->route('spmi.rtm.show', $rtm)->with('success', 'Action item berhasil diperbarui.');
    }

    public function transitionActionItem(Request $request, Rtm $rtm, RtmActionItem $rtmActionItem)
    {
        $validated = $request->validate([
            'status' => 'required|in:open,in_progress,completed,cancelled',
            'hasil' => 'nullable|string',
        ]);
        $data = ['status' => $validated['status']];
        if ($validated['status'] === 'completed') {
            $data['completed_at'] = now();
            $data['hasil'] = $validated['hasil'] ?? $rtmActionItem->hasil;
        }
        $rtmActionItem->update($data);
        return redirect()->route('spmi.rtm.show', $rtm)->with('success', 'Status action item berhasil diperbarui.');
    }

    public function destroyActionItem(Rtm $rtm, RtmActionItem $rtmActionItem)
    {
        $rtmActionItem->delete();
        return redirect()->route('spmi.rtm.show', $rtm)->with('success', 'Action item berhasil dihapus.');
    }
}
