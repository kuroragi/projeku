<?php

namespace App\Http\Controllers;

use App\Models\Card;
use App\Models\File;
use App\Models\Page;
use App\Models\Project;
use App\Services\ProjectAccess;
use App\Services\ReminderService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class TrashController extends Controller
{
    public function index(Project $project)
    {
        ProjectAccess::authorize($project, 'project.view');

        $trashedCards = Card::onlyTrashed()
            ->where('project_id', $project->id)
            ->latest('deleted_at')
            ->get();

        $trashedFiles = File::onlyTrashed()
            ->where('project_id', $project->id)
            ->latest('deleted_at')
            ->get();

        $trashedPages = Page::onlyTrashed()
            ->where('project_id', $project->id)
            ->latest('deleted_at')
            ->get();

        if (request()->wantsJson()) {
            return response()->json([
                'cards' => $trashedCards,
                'files' => $trashedFiles,
                'pages' => $trashedPages,
            ]);
        }

        return view('projects.trash', compact('project', 'trashedCards', 'trashedFiles', 'trashedPages'));
    }

    public function restoreCard(Request $request, Project $project, int $cardId)
    {
        ProjectAccess::authorize($project, 'trash.restore');

        $card = Card::onlyTrashed()
            ->where('project_id', $project->id)
            ->where('id', $cardId)
            ->firstOrFail();

        $card->restore();
        ReminderService::syncCardReminders($card);

        if ($request->wantsJson()) {
            return response()->json(['message' => 'Kartu berhasil dipulihkan.']);
        }

        return back()->with('status', 'Kartu berhasil dipulihkan.');
    }

    public function restoreFile(Request $request, Project $project, int $fileId)
    {
        ProjectAccess::authorize($project, 'trash.restore');

        $file = File::onlyTrashed()
            ->where('project_id', $project->id)
            ->where('id', $fileId)
            ->firstOrFail();

        $file->restore();

        if ($request->wantsJson()) {
            return response()->json(['message' => 'Berkas berhasil dipulihkan.']);
        }

        return back()->with('status', 'Berkas berhasil dipulihkan.');
    }

    public function restorePage(Request $request, Project $project, int $pageId)
    {
        ProjectAccess::authorize($project, 'trash.restore');

        $page = Page::onlyTrashed()
            ->where('project_id', $project->id)
            ->where('id', $pageId)
            ->firstOrFail();

        $page->restore();

        if ($request->wantsJson()) {
            return response()->json(['message' => 'Halaman berhasil dipulihkan.']);
        }

        return back()->with('status', 'Halaman berhasil dipulihkan.');
    }

    public function purgeCard(Request $request, Project $project, int $cardId)
    {
        ProjectAccess::authorize($project, 'trash.purge');

        $card = Card::onlyTrashed()
            ->where('project_id', $project->id)
            ->where('id', $cardId)
            ->firstOrFail();

        $card->forceDelete();

        if ($request->wantsJson()) {
            return response()->json(['message' => 'Kartu berhasil dimusnahkan secara permanen.']);
        }

        return back()->with('status', 'Kartu berhasil dimusnahkan secara permanen.');
    }

    public function purgeFile(Request $request, Project $project, int $fileId)
    {
        ProjectAccess::authorize($project, 'trash.purge');

        $file = File::onlyTrashed()
            ->where('project_id', $project->id)
            ->where('id', $fileId)
            ->firstOrFail();

        // Hapus berkas fisik dari storage
        if ($file->stored_name && Storage::disk('local')->exists($file->stored_name)) {
            Storage::disk('local')->delete($file->stored_name);
        }
        if ($file->thumb_path && Storage::disk('local')->exists($file->thumb_path)) {
            Storage::disk('local')->delete($file->thumb_path);
        }

        $file->forceDelete();

        if ($request->wantsJson()) {
            return response()->json(['message' => 'Berkas berhasil dimusnahkan secara permanen.']);
        }

        return back()->with('status', 'Berkas berhasil dimusnahkan secara permanen.');
    }

    public function purgePage(Request $request, Project $project, int $pageId)
    {
        ProjectAccess::authorize($project, 'trash.purge');

        $page = Page::onlyTrashed()
            ->where('project_id', $project->id)
            ->where('id', $pageId)
            ->firstOrFail();

        $page->forceDelete();

        if ($request->wantsJson()) {
            return response()->json(['message' => 'Halaman berhasil dimusnahkan secara permanen.']);
        }

        return back()->with('status', 'Halaman berhasil dimusnahkan secara permanen.');
    }
}
