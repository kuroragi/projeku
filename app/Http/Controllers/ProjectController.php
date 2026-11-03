<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Services\ProjectAccess;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProjectController extends Controller
{
    public function index()
    {
        $projects = ProjectAccess::visibleProjects(auth()->user())
            ->latest()
            ->get();

        if (request()->wantsJson()) {
            return response()->json($projects);
        }

        return view('projects.index', compact('projects'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'description' => ['nullable', 'string'],
            'due_at' => ['nullable', 'date'],
        ]);

        $project = DB::transaction(function () use ($validated) {
            $project = Project::create($validated);
            ProjectAccess::assignRole($project, auth()->user(), 'owner');
            return $project;
        });

        if ($request->wantsJson()) {
            return response()->json($project, 201);
        }

        return redirect()->route('projects.show', $project)->with('status', 'Projek berhasil dibuat.');
    }

    public function show(Project $project)
    {
        ProjectAccess::authorize($project, 'project.view');

        if (request()->wantsJson()) {
            return response()->json($project);
        }

        return redirect()->route('projects.board', $project);
    }

    public function update(Request $request, Project $project)
    {
        ProjectAccess::authorize($project, 'project.manage');

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'description' => ['nullable', 'string'],
            'due_at' => ['nullable', 'date'],
            'archived' => ['nullable', 'boolean'],
        ]);

        if (isset($validated['archived'])) {
            $validated['archived_at'] = $validated['archived'] ? now() : null;
            unset($validated['archived']);
        }

        $project->update($validated);

        if ($request->wantsJson()) {
            return response()->json($project);
        }

        return back()->with('status', 'Projek berhasil diperbarui.');
    }

    public function destroy(Project $project)
    {
        ProjectAccess::authorize($project, 'project.delete');

        $project->delete();

        if (request()->wantsJson()) {
            return response()->json(['message' => 'Projek berhasil dihapus.']);
        }

        return redirect()->route('projects.index')->with('status', 'Projek berhasil dipindahkan ke tempat sampah.');
    }
}

