<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\DashboardService;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
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