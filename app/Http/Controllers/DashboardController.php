<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Buoy;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        // 1. Stats Cards
        $totalBuoys = Buoy::count();
        $activeBuoys = Buoy::where('status', 'normal')->count();
        $warningBuoys = Buoy::where('status', 'warning')->count();
        
        // 2. Recent Logs (Mocking relation if not exists or using DB directly for speed)
        // Assuming 'buoy_logs' table exists as per migration
        $recentLogs = DB::table('buoy_logs')
            ->join('buoys', 'buoy_logs.buoy_id', '=', 'buoys.id')
            ->select('buoy_logs.*', 'buoys.name as buoy_name', 'buoys.device_id')
            ->orderBy('buoy_logs.logged_at', 'desc')
            ->limit(5)
            ->get();

        return view('dashboard', compact('totalBuoys', 'activeBuoys', 'warningBuoys', 'recentLogs'));
    }
}
