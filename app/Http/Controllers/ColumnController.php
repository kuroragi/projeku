<?php

namespace App\Http\Controllers;

use App\Models\Column;
use App\Models\Project;
use App\Services\ProjectAccess;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ColumnController extends Controller
{
    public function store(Request $request, Project $project)
    {
        ProjectAccess::authorize($project, 'board.edit');

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'is_done' => ['nullable', 'boolean'],
        ]);

        $maxPosition = (int) $project->columns()->max('position');
        $position = $maxPosition + 1000;

        $column = $project->columns()->create([
            'name' => $validated['name'],
            'position' => $position,
            'is_done' => $validated['is_done'] ?? false,
        ]);

        if ($request->wantsJson()) {
            return response()->json($column, 201);
        }

        return back()->with('status', 'Kolom berhasil ditambahkan.');
    }

    public function update(Request $request, Project $project, Column $column)
    {
        ProjectAccess::authorize($project, 'board.edit');

        // Pastikan kolom milik projek ini
        if ($column->project_id !== $project->id) {
            abort(404);
        }

        $validated = $request->validate([
            'name' => ['sometimes', 'required', 'string', 'max:100'],
            'is_done' => ['nullable', 'boolean'],
        ]);

        $column->update($validated);

        if ($request->wantsJson()) {
            return response()->json($column);
        }

        return back()->with('status', 'Kolom berhasil diperbarui.');
    }

    public function reorder(Request $request, Project $project)
    {
        ProjectAccess::authorize($project, 'board.edit');

        $validated = $request->validate([
            'order' => ['required', 'array'],
            'order.*.id' => ['required', 'integer'],
            'order.*.position' => ['required', 'integer'],
        ]);

        DB::transaction(function () use ($project, $validated) {
            foreach ($validated['order'] as $item) {
                $project->columns()
                    ->where('id', $item['id'])
                    ->update(['position' => $item['position']]);
            }
        });

        return response()->json(['message' => 'Urutan kolom berhasil diperbarui.']);
    }

    public function destroy(Request $request, Project $project, Column $column)
    {
        ProjectAccess::authorize($project, 'board.edit');

        if ($column->project_id !== $project->id) {
            abort(404);
        }

        // Aturan keras: Kolom tidak bisa dihapus selama masih berisi kartu (termasuk kartu aktif)
        $hasCards = $column->cards()->exists();
        if ($hasCards) {
            abort(422, 'Kolom tidak dapat dihapus selama masih berisi kartu.');
        }

        $column->delete();

        if ($request->wantsJson()) {
            return response()->json(['message' => 'Kolom berhasil dihapus.']);
        }

        return back()->with('status', 'Kolom berhasil dihapus.');
    }
}

