<?php

namespace App\Http\Controllers;

use App\Models\Card;
use App\Models\Checklist;
use App\Models\ChecklistItem;
use App\Models\Project;
use App\Services\ProjectAccess;
use Illuminate\Http\Request;

class ChecklistController extends Controller
{
    public function store(Request $request, Project $project, Card $card)
    {
        ProjectAccess::authorize($project, 'board.edit');

        if ($card->project_id !== $project->id) {
            abort(404);
        }

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:150'],
        ]);

        $maxPosition = (int) $card->checklists()->max('position');

        $checklist = $card->checklists()->create([
            'project_id' => $project->id,
            'title' => $validated['title'],
            'position' => $maxPosition + 1000,
        ]);

        $checklist->load('items');

        if ($request->wantsJson()) {
            return response()->json($checklist, 201);
        }

        return back()->with('status', 'Checklist berhasil ditambahkan.');
    }

    public function destroy(Request $request, Project $project, Card $card, Checklist $checklist)
    {
        ProjectAccess::authorize($project, 'board.edit');

        if ($checklist->project_id !== $project->id || $checklist->card_id !== $card->id) {
            abort(404);
        }

        $checklist->delete();

        if ($request->wantsJson()) {
            return response()->json(['message' => 'Checklist berhasil dihapus.']);
        }

        return back()->with('status', 'Checklist berhasil dihapus.');
    }

    public function storeItem(Request $request, Project $project, Card $card, Checklist $checklist)
    {
        ProjectAccess::authorize($project, 'board.edit');

        if ($checklist->project_id !== $project->id || $checklist->card_id !== $card->id) {
            abort(404);
        }

        $validated = $request->validate([
            'text' => ['required', 'string', 'max:255'],
        ]);

        $maxPosition = (int) $checklist->items()->max('position');

        $item = $checklist->items()->create([
            'project_id' => $project->id,
            'text' => $validated['text'],
            'is_done' => false,
            'position' => $maxPosition + 1000,
        ]);

        if ($request->wantsJson()) {
            return response()->json($item, 201);
        }

        return back()->with('status', 'Item checklist berhasil ditambahkan.');
    }

    public function toggleItem(Request $request, Project $project, Card $card, Checklist $checklist, ChecklistItem $item)
    {
        ProjectAccess::authorize($project, 'board.edit');

        if ($item->project_id !== $project->id || $item->checklist_id !== $checklist->id) {
            abort(404);
        }

        $item->update([
            'is_done' => ! $item->is_done,
        ]);

        if ($request->wantsJson()) {
            return response()->json($item);
        }

        return back();
    }

    public function destroyItem(Request $request, Project $project, Card $card, Checklist $checklist, ChecklistItem $item)
    {
        ProjectAccess::authorize($project, 'board.edit');

        if ($item->project_id !== $project->id || $item->checklist_id !== $checklist->id) {
            abort(404);
        }

        $item->delete();

        if ($request->wantsJson()) {
            return response()->json(['message' => 'Item berhasil dihapus.']);
        }

        return back();
    }
}

