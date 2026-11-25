<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TeamResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'short_name' => $this->short_name,
            'tla' => $this->tla,
            'crest' => $this->crest,
            'venue' => $this->venue,
            'founded' => $this->founded,
            'club_colors' => $this->club_colors,
            'area_name' => $this->area_name,
            'players_count' => $this->whenCounted('players'),
            'competitions' => CompetitionResource::collection($this->whenLoaded('competitions')),
            'players' => PlayerResource::collection($this->whenLoaded('players')),
        ];
    }
}
