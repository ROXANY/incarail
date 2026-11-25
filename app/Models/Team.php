<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Team extends Model
{
    protected $fillable = [
        'external_id',
        'name',
        'short_name',
        'tla',
        'crest',
        'venue',
        'founded',
        'club_colors',
        'area_name',
    ];

    public function competitions(): BelongsToMany
    {
        return $this->belongsToMany(Competition::class);
    }

    public function players(): HasMany
    {
        return $this->hasMany(Player::class);
    }

    public function scopeSearch(Builder $query, ?string $search): void
    {
        if ($search) {
            $query->where('name', 'like', "%{$search}%");
        }
    }

    public function scopeInCompetition(Builder $query, ?int $competitionId): void
    {
        if ($competitionId) {
            $query->whereHas('competitions', function (Builder $competitions) use ($competitionId) {
                $competitions->where('competitions.id', $competitionId);
            });
        }
    }
}
