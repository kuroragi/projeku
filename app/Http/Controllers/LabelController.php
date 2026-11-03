<?php

namespace App\Http\Controllers;

use App\Models\Label;
use App\Models\Project;
use App\Services\ProjectAccess;
use Illuminate\Http\Request;

class LabelController extends Controller
{
    public function index(Project $project)
    {
        ProjectAccess::authorize($project, 'project.view');

        return response()->json($project->labels()->get());
    }

    public function store(Request $request, Project $project)
    {
        ProjectAccess::authorize($project, 'board.edit');

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:50'],
            'color' => ['required', 'string', 'max:20'],
        ]);

        $label = $project->labels()->create($validated);

        if ($request->wantsJson()) {
            return response()->json($label, 201);
        }

        return back()->with('status', 'Label berhasil dibuat.');
    }

    public function destroy(Request $request, Project $project, Label $label)
    {
        ProjectAccess::authorize($project, 'board.edit');

        if ($label->project_id !== $project->id) {
            abort(404);
        }

        $label->delete();

        if ($request->wantsJson()) {
            return response()->json(['message' => 'Label berhasil dihapus.']);
        }

        return back()->with('status', 'Label berhasil dihapus.');
    }
}

