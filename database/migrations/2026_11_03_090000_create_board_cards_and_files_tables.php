<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Kolom Board
        Schema::create('columns', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained('projects')->cascadeOnDelete();
            $table->string('name');
            $table->integer('position')->default(1000);
            $table->boolean('is_done')->default(false);
            $table->timestamps();
        });

        // 2. Kartu (Cards)
        Schema::create('cards', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained('projects')->cascadeOnDelete();
            $table->foreignId('column_id')->constrained('columns')->cascadeOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->integer('position')->default(1000);
            $table->string('priority', 20)->default('normal'); // low, normal, high
            $table->dateTime('due_at')->nullable();
            $table->unsignedBigInteger('page_id')->nullable();
            $table->softDeletes()->index();
            $table->timestamps();
        });

        // 3. Penanggung Jawab Kartu (Multi Assignees)
        Schema::create('card_user', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained('projects')->cascadeOnDelete();
            $table->foreignId('card_id')->constrained('cards')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['card_id', 'user_id']);
        });

        // 4. Label Projek
        Schema::create('labels', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained('projects')->cascadeOnDelete();
            $table->string('name');
            $table->string('color', 20); // kode warna hex palet label
            $table->timestamps();
        });

        // 5. Relasi Kartu dan Label
        Schema::create('card_label', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained('projects')->cascadeOnDelete();
            $table->foreignId('card_id')->constrained('cards')->cascadeOnDelete();
            $table->foreignId('label_id')->constrained('labels')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['card_id', 'label_id']);
        });

        // 6. Checklist Kartu
        Schema::create('checklists', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained('projects')->cascadeOnDelete();
            $table->foreignId('card_id')->constrained('cards')->cascadeOnDelete();
            $table->string('title');
            $table->integer('position')->default(1000);
            $table->timestamps();
        });

        // 7. Item Checklist
        Schema::create('checklist_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained('projects')->cascadeOnDelete();
            $table->foreignId('checklist_id')->constrained('checklists')->cascadeOnDelete();
            $table->string('text');
            $table->boolean('is_done')->default(false);
            $table->integer('position')->default(1000);
            $table->timestamps();
        });

        // 8. Berkas & Gambar Galeri
        Schema::create('files', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained('projects')->cascadeOnDelete();
            $table->string('original_name');
            $table->string('stored_name');
            $table->string('mime_type')->nullable();
            $table->unsignedBigInteger('size'); // byte
            $table->unsignedInteger('width')->nullable();
            $table->unsignedInteger('height')->nullable();
            $table->string('thumb_path')->nullable();
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->softDeletes()->index();
            $table->timestamps();
        });

        // 9. Lampiran Berkas Kartu
        Schema::create('card_file', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained('projects')->cascadeOnDelete();
            $table->foreignId('card_id')->constrained('cards')->cascadeOnDelete();
            $table->foreignId('file_id')->constrained('files')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['card_id', 'file_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('card_file');
        Schema::dropIfExists('files');
        Schema::dropIfExists('checklist_items');
        Schema::dropIfExists('checklists');
        Schema::dropIfExists('card_label');
        Schema::dropIfExists('labels');
        Schema::dropIfExists('card_user');
        Schema::dropIfExists('cards');
        Schema::dropIfExists('columns');
    }
};

