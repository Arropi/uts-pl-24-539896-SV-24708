<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CoralColonyController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Coral Reef Restoration Tracker RESTful API
|
*/

// Public Authentication Route
Route::post('/login', [AuthController::class, 'login']);

// Protected Routes (Sanctum)
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);

    Route::get('/user', function (Request $request) {
        return response()->json($request->user());
    });

    Route::apiResource('coral-colonies', CoralColonyController::class);
});
