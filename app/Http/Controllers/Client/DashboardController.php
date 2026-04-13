<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;

class DashboardController extends Controller
{
    public function index()
    {
        $client = auth('client')->user();

        return view('client.dashboard', compact('client'));
    }
}
