<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\ControlPlanItem;
use App\Models\ItemDocument;
use App\Services\ControlPlanAccessService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ItemDocumentController extends Controller
{
    public function __construct(
        private ControlPlanAccessService $access,
    ) {}

    public function store(Request $request, string $itemId): RedirectResponse
    {
        $item = ControlPlanItem::with('responsibles', 'plan')->findOrFail($itemId);

        abort_unless($this->access->canEditItem(auth()->user(), $item), 403);

        $request->validate([
            // MIME allow-list — ихтиёрий тур (масалан .html/.svg) юклаб stored-XSS олдини олиш.
            'file' => ['required', 'file', 'max:20480', 'mimes:pdf,doc,docx,xls,xlsx,jpg,jpeg,png'],
            'description' => ['nullable', 'string', 'max:500'],
        ]);

        $file = $request->file('file');
        // Махфий ҳужжатлар public дискда эмас — private (local) дискда сақланади,
        // фақат авторизацияланган download() маршрути орқали берилади.
        // Мустақил топшириқ (control_plan_id = null) учун алоҳида папка.
        $folder = $item->control_plan_id ? "control-plan-docs/{$item->control_plan_id}" : "task-docs/{$item->id}";
        $path = $file->store($folder, 'local');

        ItemDocument::create([
            'control_plan_item_id' => $item->id,
            'file_path' => $path,
            'original_name' => $file->getClientOriginalName(),
            'file_size' => $file->getSize(),
            'mime_type' => $file->getMimeType(),
            'description' => $request->input('description'),
            'uploaded_by' => auth()->id(),
        ]);

        return back()->with('success', 'Ҳужжат юкланди.');
    }

    public function download(string $id): BinaryFileResponse
    {
        $doc = ItemDocument::with('item.responsibles', 'item.plan')->findOrFail($id);

        abort_unless($this->access->canViewItem(auth()->user(), $doc->item), 403);

        return response()->download(
            Storage::disk('local')->path($doc->file_path),
            $doc->original_name,
        );
    }

    public function destroy(string $id): RedirectResponse
    {
        $doc = ItemDocument::with('item.responsibles', 'item.plan')->findOrFail($id);

        abort_unless($this->access->canEditItem(auth()->user(), $doc->item), 403);

        Storage::disk('local')->delete($doc->file_path);
        $doc->delete();

        return back()->with('success', 'Ҳужжат ўчирилди.');
    }
}
