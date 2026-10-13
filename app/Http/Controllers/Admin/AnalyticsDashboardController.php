<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AnalyticsEvent;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class AnalyticsDashboardController extends Controller
{
    public function index(): JsonResponse
    {
        $today = now()->startOfDay();
        $last7Days = now()->subDays(6)->startOfDay();

        $events = AnalyticsEvent::query()
            ->where('event_name', 'page_view');

        $totalViews = (clone $events)->count();

        $uniqueVisitors = (clone $events)
            ->distinct('visitor_id')
            ->count('visitor_id');

        $totalSessions = (clone $events)
            ->distinct('session_id')
            ->count('session_id');

        $todayViews = (clone $events)
            ->where('created_at', '>=', $today)
            ->count();

        $todayVisitors = (clone $events)
            ->where('created_at', '>=', $today)
            ->distinct('visitor_id')
            ->count('visitor_id');

        $dailyViews = (clone $events)
            ->where('created_at', '>=', $last7Days)
            ->selectRaw('DATE(created_at) as date, COUNT(*) as views')
            ->groupBy(DB::raw('DATE(created_at)'))
            ->orderBy('date')
            ->get();

        $topPages = (clone $events)
            ->select('page_path')
            ->selectRaw('COUNT(*) as views')
            ->groupBy('page_path')
            ->orderByDesc('views')
            ->limit(10)
            ->get();

        return response()->json([
            'data' => [
                'total_views' => $totalViews,
                'unique_visitors' => $uniqueVisitors,
                'total_sessions' => $totalSessions,
                'today_views' => $todayViews,
                'today_visitors' => $todayVisitors,
                'daily_views' => $dailyViews,
                'top_pages' => $topPages,
            ],
        ]);
    }
}