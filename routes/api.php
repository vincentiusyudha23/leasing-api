<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DeviceController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::prefix('device')->group(function(){
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
    Route::get('/info/{deviceId}', [DeviceController::class, 'get_info_device'])->middleware('auth.device');
});

Route::middleware(['auth.device'])->prefix('leasing')->group(function(){
    Route::post('/add-leasing-plan/{deviceId}', [DeviceController::class, 'add_leasing_plan']);
    Route::post('/update/{leasingId}/{deviceId}', [DeviceController::class, 'update_leasing_period']);
});
