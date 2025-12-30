<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\BuoyController;
use App\Http\Controllers\Api\ShipLocationController;

Route::get('/', function () {
    return redirect('/login');;
});

Route::get('/dashboard', [\App\Http\Controllers\DashboardController::class, 'index'])
    ->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');


    // Tampilan Map 
    Route::get('/map', function () {
        return view('map');
    })->name('map');

    // Tampilan Tracking Ship
    Route::get('/ship/tracking', function () {
        return view('ship');
    })->name('ship.tracking');

    // Buoy CRUD
    Route::resource('buoys', \App\Http\Controllers\BuoyController::class);

    // Buoy Logs
    Route::get('/logs', [\App\Http\Controllers\BuoyLogController::class, 'index'])->name('logs.index');
});

Route::prefix('api')->group(function () {
    Route::get('/buoys', [BuoyController::class, 'index']);
    Route::post('/buoys', [BuoyController::class, 'store']);
    Route::put('/buoys/{id}', [BuoyController::class, 'update']);
    Route::delete('/buoys/{id}', [BuoyController::class, 'delete']);

    // Update Lokasi Ship (HP)
    Route::post('/ship/update-location', [ShipLocationController::class, 'update']);

    // Data dummy 
    Route::get('/buoys-dummy', function () {
        return [
            [
                "device_id" => "BUOY-001",
                "name" => "Buoy A",
                "lat" => -6.28620,
                "lng" => 107.82087,
                "radius" => 200,
                "status" => "normal"
            ],
            [
                "device_id" => "BUOY-002",
                "name" => "Buoy B",
                "lat" => -6.21100,
                "lng" => 106.82100,
                "radius" => 250,
                "status" => "warning"
            ]
        ];
    });
});

require __DIR__ . '/auth.php';
