<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\BuoyController;

Route::get('/', function () {
    return redirect('/login');;
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Tampilan Bouy 
    Route::get('/map', function () {
        return view('map');
    })->name('map');
});

Route::prefix('api')->group(function () {
    Route::get('/buoys', [BuoyController::class, 'index']);
    Route::post('/buoys', [BuoyController::class, 'store']);
    Route::put('/buoys/{id}', [BuoyController::class, 'update']);
    Route::delete('/buoys/{id}', [BuoyController::class, 'delete']);

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
