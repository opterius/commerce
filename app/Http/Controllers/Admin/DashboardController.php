<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Client;

class DashboardController extends Controller
{
    public function index()
    {
        $totalClients = Client::count();
        $activeClients = Client::where('status', 'active')->count();
        $recentActivity = ActivityLog::with('staff')
            ->latest('created_at')
            ->take(10)
            ->get();

        return view('admin.dashboard', compact(
            'totalClients',
            'activeClients',
            'recentActivity'
        ));
    }
}
