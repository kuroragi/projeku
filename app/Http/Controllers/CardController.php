<?php

namespace App\Http\Controllers;

use App\Models\Card;
use App\Models\Column;
use App\Models\Project;
use App\Services\ProjectAccess;
use App\Services\ReminderService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CardController extends Controller
{
    public function index(Project $project)
    {
        ProjectAccess::authorize($project, 'project.view');

        $columns = $project->columns()
            ->with(['cards' => function ($q) {
                $q->with(['labels', 'assignees', 'checklists.items', 'files'])->orderBy('position');
            }])
            ->orderBy('position')
            ->get();

        if (request()->wantsJson()) {
            return response()->json($columns);
        }

        $labels = $project->labels()->get();
        $pages = $project->pages()->select('id', 'title')->orderBy('position')->get();
        $members = DB::table('model_has_roles')
            ->join('users', 'users.id', '=', 'model_has_roles.model_id')
            ->where('model_has_roles.project_id', $project->id)
            ->whereNull('users.deactivated_at')
            ->select('users.id', 'users.name', 'users.email')
            ->distinct()
            ->get();

        return view('projects.board', compact('project', 'columns', 'labels', 'members', 'pages'));
    }

    public function store(Request $request, Project $project, Column $column)
    {
        ProjectAccess::authorize($project, 'board.edit');

        if ($column->project_id !== $project->id) {
            abort(404);
        }

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'priority' => ['nullable', 'in:low,normal,high'],
            'due_at' => ['nullable', 'date'],
            'page_id' => ['nullable', 'integer', 'exists:pages,id'],
            'label_ids' => ['nullable', 'array'],
            'label_ids.*' => ['integer', 'exists:labels,id'],
            'assignee_ids' => ['nullable', 'array'],
            'assignee_ids.*' => ['integer', 'exists:users,id'],
        ]);

        $maxPosition = (int) $column->cards()->max('position');
        $position = $maxPosition + 1000;

        $card = DB::transaction(function () use ($project, $column, $validated, $position) {
            $card = $column->cards()->create([
                'project_id' => $project->id,
                'title' => $validated['title'],
                'description' => $validated['description'] ?? null,
                'priority' => $validated['priority'] ?? 'normal',
                'due_at' => $validated['due_at'] ?? null,
                'page_id' => $validated['page_id'] ?? null,
                'position' => $position,
            ]);

            if (! empty($validated['label_ids'])) {
                $syncLabels = [];
                foreach ($validated['label_ids'] as $lId) {
                    $syncLabels[$lId] = ['project_id' => $project->id];
                }
                $card->labels()->sync($syncLabels);
            }

            if (! empty($validated['assignee_ids'])) {
                $syncAssignees = [];
                foreach ($validated['assignee_ids'] as $uId) {
                    $syncAssignees[$uId] = ['project_id' => $project->id];
                }
                $card->assignees()->sync($syncAssignees);
            }

            ReminderService::syncCardReminders($card);

            return $card;
        });

        $card->load(['labels', 'assignees', 'checklists.items', 'files', 'page']);

        if ($request->wantsJson()) {
            return response()->json($card, 201);
        }

        return back()->with('status', 'Kartu berhasil dibuat.');
    }

    public function show(Project $project, Card $card)
    {
        ProjectAccess::authorize($project, 'project.view');

        if ($card->project_id !== $project->id) {
            abort(404);
        }

        $card->load(['labels', 'assignees', 'checklists.items', 'files', 'page']);

        return response()->json($card);
    }

    public function update(Request $request, Project $project, Card $card)
    {
        ProjectAccess::authorize($project, 'board.edit');

        if ($card->project_id !== $project->id) {
            abort(404);
        }

        $validated = $request->validate([
            'title' => ['sometimes', 'required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'priority' => ['nullable', 'in:low,normal,high'],
            'due_at' => ['nullable', 'date'],
            'page_id' => ['nullable', 'integer', 'exists:pages,id'],
            'label_ids' => ['nullable', 'array'],
            'label_ids.*' => ['integer', 'exists:labels,id'],
            'assignee_ids' => ['nullable', 'array'],
            'assignee_ids.*' => ['integer', 'exists:users,id'],
        ]);

        DB::transaction(function () use ($card, $validated) {
            $dataToUpdate = [
                'title' => $validated['title'] ?? $card->title,
                'description' => array_key_exists('description', $validated) ? $validated['description'] : $card->description,
                'priority' => $validated['priority'] ?? $card->priority,
                'due_at' => array_key_exists('due_at', $validated) ? $validated['due_at'] : $card->due_at,
            ];

            if (array_key_exists('page_id', $validated)) {
                $dataToUpdate['page_id'] = $validated['page_id'];
            }

            $card->update($dataToUpdate);

            if (isset($validated['label_ids'])) {
                $syncLabels = [];
                foreach ($validated['label_ids'] as $lId) {
                    $syncLabels[$lId] = ['project_id' => $card->project_id];
                }
                $card->labels()->sync($syncLabels);
            }

            if (isset($validated['assignee_ids'])) {
                $syncAssignees = [];
                foreach ($validated['assignee_ids'] as $uId) {
                    $syncAssignees[$uId] = ['project_id' => $card->project_id];
                }
                $card->assignees()->sync($syncAssignees);
            }

            ReminderService::syncCardReminders($card);
        });

        $card->load(['labels', 'assignees', 'checklists.items', 'files', 'page']);

        if ($request->wantsJson()) {
            return response()->json($card);
        }

        return back()->with('status', 'Kartu berhasil diperbarui.');
    }

    public function move(Request $request, Project $project, Card $card)
    {
        ProjectAccess::authorize($project, 'board.edit');

        if ($card->project_id !== $project->id) {
            abort(404);
        }

        $validated = $request->validate([
            'column_id' => ['required', 'integer', 'exists:columns,id'],
            'position' => ['required', 'integer'],
        ]);

        $targetColumn = Column::where('id', $validated['column_id'])
            ->where('project_id', $project->id)
            ->firstOrFail();

        $card->update([
            'column_id' => $targetColumn->id,
            'position' => $validated['position'],
        ]);

        ReminderService::syncCardReminders($card);

        return response()->json(['message' => 'Kartu berhasil dipindahkan.', 'card' => $card]);
    }

    public function destroy(Request $request, Project $project, Card $card)
    {
        ProjectAccess::authorize($project, 'board.edit');

        if ($card->project_id !== $project->id) {
            abort(404);
        }

        ReminderService::cancelCardReminders($card);

        $card->delete();

        if ($request->wantsJson()) {
            return response()->json(['message' => 'Kartu dipindahkan ke tempat sampah.']);
        }

        return back()->with('status', 'Kartu dipindahkan ke tempat sampah.');
    }
}
