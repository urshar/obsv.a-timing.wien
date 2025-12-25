<?php

namespace App\Http\Controllers;

use App\Models\ImportBatch;
use App\Models\ImportMapping;
use App\Rules\LenexFile;
use App\Services\Lenex\LenexImportService;
use App\Support\Lenex\LenexUploadReader;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Throwable;

class LenexImportController extends Controller
{
    public function create()
    {
        return view('imports.lenex.upload');
    }

    /**
     * @throws Throwable
     */
    public function store(Request $request, LenexImportService $service)
    {
        $request->validate([
            'lenex_file' => ['required', 'file', 'max:20480', new LenexFile],
            'forced_type' => ['nullable', 'in:meet_structure,entries,results,records'],
        ]);

        $file = $request->file('lenex_file');
        $filename = $file->getClientOriginalName();

        $xmlString = LenexUploadReader::toXmlString($file);

        $batch = $service->createPreviewFromUpload(
            $filename,
            $xmlString,
            $request->input('forced_type')
        );

        $path = "imports/lenex/batch_{$batch->id}.xml";
        Storage::disk('local')->put($path, $xmlString);

        return redirect()->route('imports.lenex.preview', $batch);
    }

    public function preview(ImportBatch $batch)
    {
        $issues = $batch->issues()->orderBy('severity', 'desc')->get()->groupBy('entity_type');
        $mappings = $batch->mappings()->get()->groupBy('entity_type');

        return view('imports.lenex.preview', compact('batch', 'issues', 'mappings'));
    }

    public function map(Request $request, ImportBatch $batch)
    {
        // We validate both:
        // - target_id (used by select dropdown)
        // - target_id_{entity_type} (used by manual link input)
        $request->validate([
            'entity_type' => ['required', 'in:meet,facility,club,athlete'],
            'source_key' => ['required', 'string', 'max:255'],
            'action' => ['required', 'in:create,link,ignore'],

            // dropdown link
            'target_id' => ['nullable', 'integer'],

            // manual link variants
            'target_id_meet' => ['nullable', 'integer'],
            'target_id_facility' => ['nullable', 'integer'],
            'target_id_club' => ['nullable', 'integer'],
            'target_id_athlete' => ['nullable', 'integer'],
        ]);

        $entityType = (string) $request->input('entity_type');
        $action = (string) $request->input('action');

        // Optional improvement: accept manual-link input name target_id_{entityType}
        $targetId = $request->input('target_id');
        if ($targetId === null) {
            $targetId = $request->input('target_id_'.strtolower($entityType));
        }

        ImportMapping::updateOrCreate(
            [
                'import_batch_id' => $batch->id,
                'entity_type' => $entityType,
                'source_key' => (string) $request->input('source_key'),
            ],
            [
                'action' => $action,
                'target_id' => $action === 'link' ? $targetId : null,
            ]
        );

        return back();
    }

    /**
     * @throws Throwable
     */
    public function commit(ImportBatch $batch, LenexImportService $service)
    {
        $path = "imports/lenex/batch_{$batch->id}.xml";
        if (! Storage::disk('local')->exists($path)) {
            return back()->withErrors(['file' => 'Stored XML not found for this batch.']);
        }

        $xmlString = Storage::disk('local')->get($path);

        $service->commit($batch, $xmlString);

        return redirect()->route('imports.lenex.preview', $batch)->with('status', 'Import committed.');
    }
}
