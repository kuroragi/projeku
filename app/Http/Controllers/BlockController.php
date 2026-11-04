<?php

namespace App\Http\Controllers;

use App\Models\Block;
use App\Models\Page;
use App\Models\Project;
use App\Services\ProjectAccess;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BlockController extends Controller
{
    public function store(Request $request, Project $project, Page $page)
    {
        ProjectAccess::authorize($project, 'plan.edit');

        if ($page->project_id !== $project->id) {
            abort(404);
        }

        $validated = $request->validate([
            'type' => ['required', 'in:judul,teks,gambar,daftar'],
            'content' => ['required', 'array'],
        ]);

        $maxPos = (int) $page->blocks()->max('position');

        $block = $page->blocks()->create([
            'project_id' => $project->id,
            'type' => $validated['type'],
            'content' => $validated['content'],
            'position' => $maxPos + 1000,
        ]);

        if ($request->wantsJson()) {
            return response()->json($block, 201);
        }

        return back()->with('status', 'Blok berhasil ditambahkan.');
    }

    public function update(Request $request, Project $project, Page $page, Block $block)
    {
        ProjectAccess::authorize($project, 'plan.edit');

        if ($page->project_id !== $project->id || $block->page_id !== $page->id) {
            abort(404);
        }

        $validated = $request->validate([
            'content' => ['required', 'array'],
        ]);

        $block->update([
            'content' => $validated['content'],
        ]);

        if ($request->wantsJson()) {
            return response()->json($block);
        }

        return back()->with('status', 'Blok berhasil diperbarui.');
    }

    public function reorder(Request $request, Project $project, Page $page)
    {
        ProjectAccess::authorize($project, 'plan.edit');

        if ($page->project_id !== $project->id) {
            abort(404);
        }

        $validated = $request->validate([
            'order' => ['required', 'array'],
            'order.*.id' => ['required', 'integer'],
            'order.*.position' => ['required', 'integer'],
        ]);

        DB::transaction(function () use ($page, $validated) {
            foreach ($validated['order'] as $item) {
                $page->blocks()
                    ->where('id', $item['id'])
                    ->update(['position' => $item['position']]);
            }
        });

        return response()->json(['message' => 'Urutan blok berhasil diperbarui.']);
    }

    public function destroy(Request $request, Project $project, Page $page, Block $block)
    {
        ProjectAccess::authorize($project, 'plan.edit');

        if ($page->project_id !== $project->id || $block->page_id !== $page->id) {
            abort(404);
        }

        $block->delete();

        if ($request->wantsJson()) {
            return response()->json(['message' => 'Blok berhasil dihapus.']);
        }

        return back()->with('status', 'Blok berhasil dihapus.');
    }
}
