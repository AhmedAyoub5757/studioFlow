<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\DashboardService;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    /**
     * @OA\Get(
     *     path="/dashboard",
     *     tags={"Dashboard"},
     *     summary="Role-specific aggregated dashboard",
     *     description="Payload shape varies by role: agency_overview, project_manager, staff, qa, finance, or client.",
     *     security={{"sanctum":{}}},
     *     @OA\Response(response=200, description="Dashboard data, shape depends on caller's role")
     * )
     */
    /**
     * @var \App\Services\DashboardService
     */
    protected $dashboard;

    /**
     * Create a new controller instance.
     *
     * @param  \App\Services\DashboardService  $dashboard
     * @return void
     */
    public function __construct(DashboardService $dashboard)
    {
        $this->dashboard = $dashboard;
    }

    /**
     * Display the dashboard dataset for the authenticated user.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        return response()->json([
            'data' => $this->dashboard->build($request->user()),
        ]);
    }
}
