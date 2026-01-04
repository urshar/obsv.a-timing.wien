<?php

namespace App\Http\Controllers;

use App\Http\Requests\MeetStoreRequest;
use App\Http\Requests\MeetUpdateRequest;
use App\Models\Meet;
use App\Support\MeetDeletionGuard;
use Illuminate\Http\Request;
use Throwable;

class MeetController extends Controller
{
    public function index(Request $request)
    {
        $q = trim((string) $request->query('q', ''));

        $meets = Meet::query()
            ->when($q !== '', function ($query) use ($q) {
                $query->where(function ($sub) use ($q) {
                    $sub->where('name', 'like', "%{$q}%")
                        ->orWhere('source_filename', 'like', "%{$q}%");
                });
            })
            ->withCount(['sessions', 'events', 'ageGroups'])
            ->orderByDesc('start_date')
            ->orderByDesc('id')
            ->paginate(20)
            ->withQueryString();

        return view('meets.index', [
            'meets' => $meets,
            'q' => $q,
        ]);
    }

    public function store(MeetStoreRequest $request)
    {
        $data = $request->validated();

        // manuell erstellte Meets haben keine LENEX Source
        $data['source_filename'] = null;
        $data['source_hash'] = null;

        $meet = Meet::create($data);

        return redirect()
            ->route('meets.edit', $meet)
            ->with('status', 'Meeting created.');
    }

    public function create()
    {
        return view('meets.create');
    }

    public function edit(Meet $meet)
    {
        return view('meets.edit', [
            'meet' => $meet,
        ]);
    }

    public function update(MeetUpdateRequest $request, Meet $meet)
    {
        $meet->update($request->validated());

        return redirect()
            ->route('meets.edit', $meet)
            ->with('status', 'Meeting updated.');
    }

    /**
     * @throws Throwable
     */
    public function destroy(Meet $meet)
    {
        $reasons = MeetDeletionGuard::reasons($meet);

        if (! empty($reasons)) {
            return redirect()
                ->route('meets.show', $meet)
                ->withErrors([
                    'delete' => 'Cannot delete this meeting: '.implode(' ', $reasons),
                ]);
        }

        $meet->delete();

        return redirect()
            ->route('meets.index')
            ->with('status', 'Meeting deleted.');
    }

    public function show(Request $request, Meet $meet)
    {
        $meet->loadCount(['sessions', 'events', 'ageGroups']);

        $q = trim((string) $request->query('q', ''));
        $type = $request->query('type');
        $status = $request->query('status');

        $batchesQuery = $meet->importBatches()
            ->when($q !== '', function ($query) use ($q) {
                $query->where(function ($qq) use ($q) {
                    $qq->where('filename', 'like', "%{$q}%")
                        ->orWhere('type', 'like', "%{$q}%")
                        ->orWhere('status', 'like', "%{$q}%");
                });
            })
            ->when(! empty($type), fn ($query) => $query->where('type', $type))
            ->when(! empty($status), fn ($query) => $query->where('status', $status))
            ->withCount([
                'issues as error_count' => fn ($q) => $q->where('severity', 'error'),
                'issues as warning_count' => fn ($q) => $q->where('severity', 'warning'),
            ])
            ->orderByDesc('id');

        $batches = $batchesQuery->paginate(15)->withQueryString();

        $latestBatch = $meet->importBatches()
            ->orderByDesc('id')
            ->first();

        $latestStructureBatch = $meet->importBatches()
            ->where('status', 'committed')
            ->where('type', 'meet_structure')
            ->orderByDesc('id')
            ->first();

        $typeOptions = $meet->importBatches()->select('type')->distinct()->orderBy('type')->pluck('type')->filter()->values();
        $statusOptions = $meet->importBatches()->select('status')->distinct()->orderBy('status')->pluck('status')->filter()->values();

        return view('meets.show', [
            'meet' => $meet,
            'batches' => $batches,

            'latestBatch' => $latestBatch,
            'latestStructureBatch' => $latestStructureBatch,

            'q' => $q,
            'type' => $type,
            'status' => $status,
            'typeOptions' => $typeOptions,
            'statusOptions' => $statusOptions,
        ]);
    }
}
