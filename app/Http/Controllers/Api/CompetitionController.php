<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\CompetitionResource;
use App\Models\Competition;
use App\Services\CompetitionService;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class CompetitionController extends Controller
{
    public function __construct(private CompetitionService $competitionService) {}

    public function index(): AnonymousResourceCollection
    {
        $result = $this->competitionService->list();

        return CompetitionResource::collection($result['competitions'])
            ->additional(['meta' => ['stale' => $result['stale']]]);
    }

    public function show(Competition $competition): CompetitionResource
    {
        $competition = $this->competitionService->detail($competition);

        return new CompetitionResource($competition);
    }
}
