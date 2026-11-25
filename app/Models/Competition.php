<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Competition extends Model
{
    protected $fillable = [
        'external_id',
        'code',
        'name',
        'type',
        'area_name',
        'emblem',
        'season_start',
        'season_end',
        'teams_synced_at',
    ];

    protected function casts(): array
    {
        return [
            'season_start' => 'date',
            'season_end' => 'date',
            'teams_synced_at' => 'datetime',
        ];
    }

    public function teams(): BelongsToMany
    {
        return $this->belongsToMany(Team::class);
    }
}
