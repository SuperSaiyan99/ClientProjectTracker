<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreProjectRequest;
use App\Http\Requests\UpdateProjectRequest;
use App\Http\Resources\ProjectResource;
use App\Models\Project;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ProjectController extends Controller
{
    /**
     * Get base project query scoped to current authenticated user.
     */
    private function projectQuery(Request $request)
    {
        $user = $request->user();

        if ($user) {
            return $user->projects();
        }

        return Project::query();
    }

    /**
     * Display a listing of the user's projects with optional filtering and sorting.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $query = $this->projectQuery($request);

        // Filter by status if provided
        if ($request->filled('status')) {
            $query->where('status', $request->query('status'));
        }

        // Filter by priority if provided
        if ($request->filled('priority')) {
            $query->where('priority', $request->query('priority'));
        }

        // Search query across client_name, project_name, and description
        if ($request->filled('search')) {
            $search = $request->query('search');
            $query->where(function ($q) use ($search) {
                $q->where('client_name', 'like', "%{$search}%")
                  ->orWhere('project_name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // Sorting
        $allowedSorts = ['id', 'client_name', 'project_name', 'status', 'priority', 'start_date', 'due_date', 'created_at'];
        $sort = $request->query('sort', 'created_at');
        $direction = strtolower($request->query('direction', 'desc')) === 'asc' ? 'asc' : 'desc';

        if (str_starts_with($sort, '-')) {
            $sort = substr($sort, 1);
            $direction = 'desc';
        }

        if (in_array($sort, $allowedSorts, true)) {
            $query->orderBy($sort, $direction);
        } else {
            $query->orderBy('created_at', 'desc');
        }

        // Compute statistics on the database side (scoped to user)
        $statsQuery = $this->projectQuery($request);
        $statusCounts = $statsQuery->selectRaw('status, count(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status')
            ->all();

        $stats = [
            'total' => (int) array_sum($statusCounts),
            'planning' => (int) ($statusCounts[Project::STATUS_PLANNING] ?? 0),
            'inProgress' => (int) ($statusCounts[Project::STATUS_IN_PROGRESS] ?? 0),
            'onHold' => (int) ($statusCounts[Project::STATUS_ON_HOLD] ?? 0),
            'completed' => (int) ($statusCounts[Project::STATUS_COMPLETED] ?? 0),
        ];

        $perPage = (int) $request->query('per_page', 10);
        $projects = $query->paginate($perPage);

        return ProjectResource::collection($projects)->additional([
            'meta' => [
                'statuses' => Project::STATUSES,
                'priorities' => Project::PRIORITIES,
                'stats' => $stats,
            ],
        ]);
    }

    /**
     * Get available metadata (statuses & priorities).
     */
    public function meta(): JsonResponse
    {
        return response()->json([
            'statuses' => Project::STATUSES,
            'priorities' => Project::PRIORITIES,
        ]);
    }

    /**
     * Store a newly created project for the authenticated user.
     */
    public function store(StoreProjectRequest $request): JsonResponse
    {
        $user = $request->user();
        
        if ($user) {
            $project = $user->projects()->create($request->validated());
        } else {
            $project = Project::create($request->validated());
        }

        return (new ProjectResource($project))
            ->response()
            ->setStatusCode(201);
    }

    /**
     * Display the specified project.
     */
    public function show(Request $request, string $id): JsonResponse|ProjectResource
    {
        $project = $this->projectQuery($request)->find($id);

        if (! $project) {
            return response()->json([
                'message' => 'Project not found',
            ], 404);
        }

        return new ProjectResource($project);
    }

    /**
     * Update the specified project in storage.
     */
    public function update(UpdateProjectRequest $request, string $id): JsonResponse|ProjectResource
    {
        $project = $this->projectQuery($request)->find($id);

        if (! $project) {
            return response()->json([
                'message' => 'Project not found',
            ], 404);
        }

        $project->update($request->validated());

        return new ProjectResource($project);
    }

    /**
     * Remove the specified project from storage.
     */
    public function destroy(Request $request, string $id): JsonResponse
    {
        $project = $this->projectQuery($request)->find($id);

        if (! $project) {
            return response()->json([
                'message' => 'Project not found',
            ], 404);
        }

        $project->delete();

        return response()->json([
            'message' => 'Project deleted successfully',
        ], 200);
    }
}
