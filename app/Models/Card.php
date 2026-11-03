<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Card extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'project_id',
        'column_id',
        'title',
        'description',
        'position',
        'priority',
        'due_at',
        'page_id',
    ];

    protected function casts(): array
    {
        return [
            'position' => 'integer',
            'due_at' => 'datetime',
        ];
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function column(): BelongsTo
    {
        return $this->belongsTo(Column::class);
    }

    public function assignees(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'card_user')->withTimestamps();
    }

    public function labels(): BelongsToMany
    {
        return $this->belongsToMany(Label::class, 'card_label')->withTimestamps();
    }

    public function checklists(): HasMany
    {
        return $this->hasMany(Checklist::class)->orderBy('position');
    }

    public function files(): BelongsToMany
    {
        return $this->belongsToMany(File::class, 'card_file')->withTimestamps();
    }
}

