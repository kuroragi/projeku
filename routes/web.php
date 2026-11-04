<?php

use App\Http\Controllers\AdminUserController;
use App\Http\Controllers\BlockController;
use App\Http\Controllers\CardController;
use App\Http\Controllers\ChecklistController;
use App\Http\Controllers\ColumnController;
use App\Http\Controllers\FileController;
use App\Http\Controllers\LabelController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\ProjectMemberController;
use App\Http\Controllers\TrashController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('projects.index');
});

Route::middleware(['auth'])->group(function () {
    // Preferensi Pengguna
    Route::patch('/user/preferences', function (\Illuminate\Http\Request $request) {
        $validated = $request->validate([
            'notify_email' => ['required', 'boolean'],
        ]);
        auth()->user()->update($validated);
        if ($request->wantsJson()) {
            return response()->json(['message' => 'Preferensi notifikasi berhasil diperbarui.', 'notify_email' => auth()->user()->notify_email]);
        }
        return back()->with('status', 'Preferensi notifikasi email berhasil diperbarui.');
    })->name('user.preferences.update');

    // Projek
    Route::get('/projects', [ProjectController::class, 'index'])->name('projects.index');
    Route::post('/projects', [ProjectController::class, 'store'])->name('projects.store');
    Route::get('/projects/{project}', [ProjectController::class, 'show'])->name('projects.show');
    Route::patch('/projects/{project}', [ProjectController::class, 'update'])->name('projects.update');
    Route::delete('/projects/{project}', [ProjectController::class, 'destroy'])->name('projects.destroy');

    // Anggota Projek
    Route::post('/projects/{project}/members', [ProjectMemberController::class, 'store'])->name('projects.members.store');
    Route::patch('/projects/{project}/members/{user}', [ProjectMemberController::class, 'update'])->name('projects.members.update');
    Route::delete('/projects/{project}/members/{user}', [ProjectMemberController::class, 'destroy'])->name('projects.members.destroy');

    // Board & Kolom (Tahap 2)
    Route::get('/projects/{project}/board', [CardController::class, 'index'])->name('projects.board');
    Route::post('/projects/{project}/columns', [ColumnController::class, 'store'])->name('projects.columns.store');
    Route::patch('/projects/{project}/columns/{column}', [ColumnController::class, 'update'])->name('projects.columns.update');
    Route::delete('/projects/{project}/columns/{column}', [ColumnController::class, 'destroy'])->name('projects.columns.destroy');
    Route::post('/projects/{project}/columns/reorder', [ColumnController::class, 'reorder'])->name('projects.columns.reorder');

    // Kartu (Tahap 2)
    Route::post('/projects/{project}/columns/{column}/cards', [CardController::class, 'store'])->name('projects.cards.store');
    Route::get('/projects/{project}/cards/{card}', [CardController::class, 'show'])->name('projects.cards.show');
    Route::patch('/projects/{project}/cards/{card}', [CardController::class, 'update'])->name('projects.cards.update');
    Route::post('/projects/{project}/cards/{card}/move', [CardController::class, 'move'])->name('projects.cards.move');
    Route::delete('/projects/{project}/cards/{card}', [CardController::class, 'destroy'])->name('projects.cards.destroy');

    // Checklist Kartu (Tahap 2)
    Route::post('/projects/{project}/cards/{card}/checklists', [ChecklistController::class, 'store'])->name('projects.checklists.store');
    Route::delete('/projects/{project}/cards/{card}/checklists/{checklist}', [ChecklistController::class, 'destroy'])->name('projects.checklists.destroy');
    Route::post('/projects/{project}/cards/{card}/checklists/{checklist}/items', [ChecklistController::class, 'storeItem'])->name('projects.checklist-items.store');
    Route::patch('/projects/{project}/cards/{card}/checklists/{checklist}/items/{item}', [ChecklistController::class, 'toggleItem'])->name('projects.checklist-items.toggle');
    Route::delete('/projects/{project}/cards/{card}/checklists/{checklist}/items/{item}', [ChecklistController::class, 'destroyItem'])->name('projects.checklist-items.destroy');

    // Label Projek (Tahap 2)
    Route::get('/projects/{project}/labels', [LabelController::class, 'index'])->name('projects.labels.index');
    Route::post('/projects/{project}/labels', [LabelController::class, 'store'])->name('projects.labels.store');
    Route::delete('/projects/{project}/labels/{label}', [LabelController::class, 'destroy'])->name('projects.labels.destroy');

    // Berkas & Galeri (Tahap 2)
    Route::get('/projects/{project}/files', [FileController::class, 'index'])->name('projects.files.index');
    Route::post('/projects/{project}/files', [FileController::class, 'store'])->name('projects.files.store');
    Route::get('/projects/{project}/files/{file}', [FileController::class, 'serve'])->name('projects.files.serve');
    Route::delete('/projects/{project}/files/{file}', [FileController::class, 'destroy'])->name('projects.files.destroy');

    // Halaman Perencanaan & Blok (Tahap 4)
    Route::get('/projects/{project}/planning', [PageController::class, 'index'])->name('projects.planning.index');
    Route::post('/projects/{project}/planning', [PageController::class, 'store'])->name('projects.planning.store');
    Route::get('/projects/{project}/planning/{page}', [PageController::class, 'show'])->name('projects.pages.show');
    Route::patch('/projects/{project}/planning/{page}', [PageController::class, 'update'])->name('projects.pages.update');
    Route::delete('/projects/{project}/planning/{page}', [PageController::class, 'destroy'])->name('projects.pages.destroy');
    Route::get('/projects/{project}/planning/{page}/presentation', [PageController::class, 'presentation'])->name('projects.pages.presentation');

    Route::post('/projects/{project}/planning/{page}/blocks', [BlockController::class, 'store'])->name('projects.blocks.store');
    Route::patch('/projects/{project}/planning/{page}/blocks/{block}', [BlockController::class, 'update'])->name('projects.blocks.update');
    Route::delete('/projects/{project}/planning/{page}/blocks/{block}', [BlockController::class, 'destroy'])->name('projects.blocks.destroy');
    Route::post('/projects/{project}/planning/{page}/blocks/reorder', [BlockController::class, 'reorder'])->name('projects.blocks.reorder');

    // Tempat Sampah / Trash
    Route::get('/projects/{project}/trash', [TrashController::class, 'index'])->name('projects.trash.index');
    Route::post('/projects/{project}/trash/cards/{id}/restore', [TrashController::class, 'restoreCard'])->name('projects.trash.cards.restore');
    Route::post('/projects/{project}/trash/files/{id}/restore', [TrashController::class, 'restoreFile'])->name('projects.trash.files.restore');
    Route::post('/projects/{project}/trash/pages/{id}/restore', [TrashController::class, 'restorePage'])->name('projects.trash.pages.restore');
    Route::delete('/projects/{project}/trash/cards/{id}', [TrashController::class, 'purgeCard'])->name('projects.trash.cards.purge');
    Route::delete('/projects/{project}/trash/files/{id}', [TrashController::class, 'purgeFile'])->name('projects.trash.files.purge');
    Route::delete('/projects/{project}/trash/pages/{id}', [TrashController::class, 'purgePage'])->name('projects.trash.pages.purge');

    // Kelola Pengguna (Admin Only)
    Route::prefix('admin')->name('admin.')->group(function () {
        Route::get('/users', [AdminUserController::class, 'index'])->name('users.index');
        Route::post('/users', [AdminUserController::class, 'store'])->name('users.store');
        Route::post('/users/{user}/resend', [AdminUserController::class, 'resendInvite'])->name('users.resend');
        Route::post('/users/{user}/deactivate', [AdminUserController::class, 'deactivate'])->name('users.deactivate');
        Route::post('/users/{user}/activate', [AdminUserController::class, 'activate'])->name('users.activate');
    });
});
