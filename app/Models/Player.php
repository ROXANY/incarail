<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Player extends Model
{
    protected $fillable = [
        'external_id',
        'team_id',
        'name',
        'position',
        'shirt_number',
        'date_of_birth',
        'nationality',
    ];

    protected function casts(): array
    {
        return [
            'date_of_birth' => 'date',
        ];
    }

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    public function scopeSearch(Builder $query, ?string $search): void
    {
        if ($search) {
            $query->where('name', 'like', "%{$search}%");
        }
    }

    public function scopeOfTeam(Builder $query, ?int $teamId): void
    {
        if ($teamId) {
            $query->where('team_id', $teamId);
        }
    }

    public function scopeOfPosition(Builder $query, ?string $position): void
    {
        if ($position) {
            $query->where('position', $position);
        }
    }
}
