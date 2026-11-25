<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\TeamFilterRequest;
use App\Http\Resources\TeamResource;
use App\Models\Team;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class TeamController extends Controller
{
    public function index(TeamFilterRequest $request): AnonymousResourceCollection
    {
        $teams = Team::query()
            ->search($request->input('search'))
            ->inCompetition($request->integer('competition_id'))
            ->withCount('players')
            ->orderBy('name')
            ->paginate($request->integer('per_page', 15));

        return TeamResource::collection($teams);
    }

    public function show(Team $team): TeamResource
    {
        $team->load(['competitions', 'players' => function ($query) {
            $query->orderBy('name');
        }]);

        return new TeamResource($team);
    }
}
