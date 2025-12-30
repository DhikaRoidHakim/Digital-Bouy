<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Events\ShipLocationUpdated;
use App\Models\Buoy;
use App\Models\BuoyLog;

class ShipLocationController extends Controller
{
    public function update(Request $request)
    {
        $request->validate([
            'lat' => 'required|numeric',
            'lng' => 'required|numeric',
            'device_id' => 'required|string',
            'label' => 'required|string'
        ]);

        $shipLat = $request->lat;
        $shipLng = $request->lng;

        // 1. Check Proximity to Buoys
        $buoys = Buoy::all();

        foreach ($buoys as $buoy) {
            $distance = $this->calculateDistance($shipLat, $shipLng, $buoy->lat, $buoy->lng);
            if ($distance <= $buoy->radius && $buoy->status === 'warning') {
                $recentLog = BuoyLog::where('buoy_id', $buoy->id)
                    ->where('logged_at', '>=', now()->subSeconds(10))
                    ->first();
                if (!$recentLog) {
                    BuoyLog::create([
                        'buoy_id' => $buoy->id,
                        'lat' => $shipLat,
                        'lng' => $shipLng,
                        'status' => 'warning',
                        'radius' => $buoy->radius,
                        'logged_at' => now()
                    ]);
                }
            }
        }

        $payload = [
            'lat' => $shipLat,
            'lng' => $shipLng,
            'device_id' => $request->device_id,
            'label' => $request->label,
            'updated_at' => now()->toDateTimeString()
        ];

        // 🔥 Broadcast ke Reverb
        broadcast(new ShipLocationUpdated($payload));

        return response()->json([
            'success' => true,
            'message' => 'Lokasi kapal berhasil dikirim.',
            'data' => $payload
        ]);
    }

    /**
     * Calculate distance between two points in meters using Haversine formula.
     */
    private function calculateDistance($lat1, $lon1, $lat2, $lon2)
    {
        $earthRadius = 6371000; // Meters

        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);

        $a = sin($dLat / 2) * sin($dLat / 2) +
            cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
            sin($dLon / 2) * sin($dLon / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $earthRadius * $c;
    }
}
