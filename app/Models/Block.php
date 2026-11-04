<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Block extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_id',
        'page_id',
        'type', // judul, teks, gambar, daftar
        'content',
        'position',
    ];

    protected function casts(): array
    {
        return [
            'content' => 'array',
            'position' => 'integer',
        ];
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function page(): BelongsTo
    {
        return $this->belongsTo(Page::class);
    }

    public function file(): ?File
    {
        if ($this->type === 'gambar' && ! empty($this->content['file_id'])) {
            return File::find($this->content['file_id']);
        }

        return null;
    }
}
