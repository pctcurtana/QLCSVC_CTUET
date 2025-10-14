<?php

namespace App\Http\Controllers;

use App\Services\DashboardService;
use Inertia\Inertia;

class DashboardController extends Controller
{
    /**
     * @var DashboardService
     */
    protected $dashboardService;

    /**
     * DashboardController constructor.
     *
     * @param DashboardService $dashboardService
     */
    public function __construct(DashboardService $dashboardService)
    {
        $this->dashboardService = $dashboardService;
    }

    /**
     * Display the dashboard.
     */
    public function index()
    {
        try {
            $dashboardData = $this->dashboardService->getDashboardData();

            return Inertia::render('Dashboard', $dashboardData);
        } catch (\Throwable $e) {
            return redirect()->back()->with('error', 'Lỗi khi tải dashboard: ' . $e->getMessage());
        }
    }
}
