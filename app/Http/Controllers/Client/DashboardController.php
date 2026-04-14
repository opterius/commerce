<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Announcement;

class DashboardController extends Controller
{
    public function index()
    {
        $client = auth('client')->user();

        $announcements = Announcement::active()->client()
            ->orderByDesc('published_at')
            ->limit(5)
            ->get();

        return view('client.dashboard', compact('client', 'announcements'));
    }
}
