<?php

namespace App\Http\Controllers;

use App\Models\BuoyLog;
use Illuminate\Http\Request;

class BuoyLogController extends Controller
{
    public function index()
    {
        $logs = BuoyLog::with('buoy')
            ->orderBy('logged_at', 'desc')
            ->paginate(15);

        return view('logs.index', compact('logs'));
    }
}
