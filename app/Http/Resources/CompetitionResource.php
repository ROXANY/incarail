<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CompetitionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'code' => $this->code,
            'name' => $this->name,
            'type' => $this->type,
            'area_name' => $this->area_name,
            'emblem' => $this->emblem,
            'season_start' => $this->season_start?->toDateString(),
            'season_end' => $this->season_end?->toDateString(),
            'teams_synced_at' => $this->teams_synced_at?->toIso8601String(),
            'teams' => TeamResource::collection($this->whenLoaded('teams')),
        ];
    }
}
