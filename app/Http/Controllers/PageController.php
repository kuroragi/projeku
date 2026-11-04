<?php

namespace App\Http\Controllers;

use App\Models\Page;
use App\Models\Project;
use App\Services\ProjectAccess;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PageController extends Controller
{
    public function index(Project $project)
    {
        ProjectAccess::authorize($project, 'project.view');

        $pages = $project->pages()->withCount(['blocks', 'linkedCards'])->get();

        if (request()->wantsJson()) {
            return response()->json($pages);
        }

        return view('projects.planning.index', compact('project', 'pages'));
    }

    public function store(Request $request, Project $project)
    {
        ProjectAccess::authorize($project, 'plan.edit');

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
        ]);

        $maxPosition = (int) $project->pages()->max('position');

        $page = DB::transaction(function () use ($project, $validated, $maxPosition) {
            $page = $project->pages()->create([
                'title' => $validated['title'],
                'position' => $maxPosition + 1000,
            ]);

            // Buat blok judul level 1 default
            $page->blocks()->create([
                'project_id' => $project->id,
                'type' => 'judul',
                'content' => [
                    'text' => $validated['title'],
                    'level' => 1,
                ],
                'position' => 1000,
            ]);

            return $page;
        });

        if ($request->wantsJson()) {
            return response()->json($page, 201);
        }

        return redirect()->route('projects.pages.show', [$project, $page])->with('status', 'Halaman perencanaan berhasil dibuat.');
    }

    public function show(Project $project, Page $page)
    {
        ProjectAccess::authorize($project, 'project.view');

        if ($page->project_id !== $project->id) {
            abort(404);
        }

        $page->load(['blocks' => function ($q) {
            $q->orderBy('position');
        }, 'linkedCards']);

        $availableFiles = $project->files()->latest()->get();

        if (request()->wantsJson()) {
            return response()->json([
                'page' => $page,
                'blocks' => $page->blocks,
            ]);
        }

        return view('projects.planning.show', compact('project', 'page', 'availableFiles'));
    }

    public function update(Request $request, Project $project, Page $page)
    {
        ProjectAccess::authorize($project, 'plan.edit');

        if ($page->project_id !== $project->id) {
            abort(404);
        }

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
        ]);

        $page->update($validated);

        if ($request->wantsJson()) {
            return response()->json($page);
        }

        return back()->with('status', 'Judul halaman berhasil diperbarui.');
    }

    public function destroy(Request $request, Project $project, Page $page)
    {
        ProjectAccess::authorize($project, 'plan.edit');

        if ($page->project_id !== $project->id) {
            abort(404);
        }

        $page->delete(); // Soft delete

        if ($request->wantsJson()) {
            return response()->json(['message' => 'Halaman dipindahkan ke tempat sampah.']);
        }

        return redirect()->route('projects.planning.index', $project)->with('status', 'Halaman dipindahkan ke tempat sampah.');
    }

    public function presentation(Project $project, Page $page)
    {
        ProjectAccess::authorize($project, 'project.view');

        if ($page->project_id !== $project->id) {
            abort(404);
        }

        $blocks = $page->blocks()->orderBy('position')->get();

        // Bagi blok menjadi slide: setiap blok judul level 1 memulai slide baru
        $slides = [];
        $currentSlide = [];

        foreach ($blocks as $block) {
            $isSlideBreak = ($block->type === 'judul' && ($block->content['level'] ?? 1) == 1);

            if ($isSlideBreak && ! empty($currentSlide)) {
                $slides[] = $currentSlide;
                $currentSlide = [];
            }

            $currentSlide[] = $block;
        }

        if (! empty($currentSlide)) {
            $slides[] = $currentSlide;
        }

        return view('projects.planning.presentation', compact('project', 'page', 'slides'));
    }
}
