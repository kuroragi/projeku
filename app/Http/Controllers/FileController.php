<?php

namespace App\Http\Controllers;

use App\Models\Card;
use App\Models\File;
use App\Models\Project;
use App\Services\ImageService;
use App\Services\ProjectAccess;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class FileController extends Controller
{
    public function index(Project $project)
    {
        ProjectAccess::authorize($project, 'project.view');

        $files = $project->files()->with('uploader')->latest()->paginate(30);

        if (request()->wantsJson()) {
            return response()->json($files);
        }

        return view('projects.files', compact('project', 'files'));
    }

    public function store(Request $request, Project $project)
    {
        ProjectAccess::authorize($project, 'file.upload');

        $request->validate([
            'file' => ['required', 'file', 'max:10240'], // max 10MB
            'card_id' => ['nullable', 'integer', 'exists:cards,id'],
        ]);

        $uploadedFile = $request->file('file');
        $processed = ImageService::processAndStore($uploadedFile, $project->id);

        $file = $project->files()->create([
            'original_name' => $uploadedFile->getClientOriginalName(),
            'stored_name' => $processed['stored_name'],
            'mime_type' => 'image/webp',
            'size' => $processed['size'],
            'width' => $processed['width'],
            'height' => $processed['height'],
            'thumb_path' => $processed['thumb_path'],
            'created_by' => auth()->id(),
        ]);

        if ($request->filled('card_id')) {
            $card = Card::where('id', $request->card_id)
                ->where('project_id', $project->id)
                ->first();
            if ($card) {
                $card->files()->attach($file->id, ['project_id' => $project->id]);
            }
        }

        if ($request->wantsJson()) {
            return response()->json($file, 201);
        }

        return back()->with('status', 'Berkas berhasil diunggah.');
    }

    public function serve(Request $request, Project $project, File $file)
    {
        ProjectAccess::authorize($project, 'project.view');

        if ($file->project_id !== $project->id) {
            abort(404);
        }

        $type = $request->query('thumb') ? 'thumb' : 'main';
        $relativePath = ($type === 'thumb' && $file->thumb_path) ? $file->thumb_path : $file->stored_name;

        if (! Storage::disk('local')->exists($relativePath)) {
            abort(404, 'Berkas fisik tidak ditemukan di server.');
        }

        $fullPath = Storage::disk('local')->path($relativePath);

        return response()->file($fullPath, [
            'Content-Type' => 'image/webp',
            'Cache-Control' => 'private, max-age=86400',
        ]);
    }

    public function destroy(Request $request, Project $project, File $file)
    {
        ProjectAccess::authorize($project, 'board.edit');

        if ($file->project_id !== $project->id) {
            abort(404);
        }

        $file->delete(); // Soft delete

        if ($request->wantsJson()) {
            return response()->json(['message' => 'Berkas dipindahkan ke tempat sampah.']);
        }

        return back()->with('status', 'Berkas dipindahkan ke tempat sampah.');
    }
}

