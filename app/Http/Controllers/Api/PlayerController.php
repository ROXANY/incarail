<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\PlayerFilterRequest;
use App\Http\Resources\PlayerResource;
use App\Models\Player;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class PlayerController extends Controller
{
    public function index(PlayerFilterRequest $request): AnonymousResourceCollection
    {
        $players = Player::query()
            ->with('team')
            ->search($request->input('search'))
            ->ofTeam($request->integer('team_id'))
            ->ofPosition($request->input('position'))
            ->orderBy('name')
            ->paginate($request->integer('per_page', 15));

        return PlayerResource::collection($players);
    }
}
