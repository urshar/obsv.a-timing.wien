<?php

namespace App\Http\Controllers;

use App\Models\ImportBatch;
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

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'age_date' => ['nullable', 'date'],
            'course' => ['nullable', 'string', 'max:20'],

            'contact_name' => ['nullable', 'string', 'max:255'],
            'contact_email' => ['nullable', 'email', 'max:255'],
            'contact_phone' => ['nullable', 'string', 'max:50'],
        ]);

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

    public function update(Request $request, Meet $meet)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'age_date' => ['nullable', 'date'],
            'course' => ['nullable', 'string', 'max:20'],

            'contact_name' => ['nullable', 'string', 'max:255'],
            'contact_email' => ['nullable', 'email', 'max:255'],
            'contact_phone' => ['nullable', 'string', 'max:50'],
        ]);

        $meet->update($data);

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

    public function show(Meet $meet)
    {
        $meet->loadCount(['sessions', 'events', 'ageGroups']);

        $batches = class_exists(ImportBatch::class)
            ? ImportBatch::query()
                ->where('meet_id', $meet->id)
                ->orderByDesc('id')
                ->limit(20)
                ->get()
            : collect();

        return view('meets.show', [
            'meet' => $meet,
            'batches' => $batches,
        ]);
    }
}
