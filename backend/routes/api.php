<?php

use App\Http\Controllers\OrderController;
use App\Http\Controllers\OrderItemController;
use App\Http\Controllers\SessionController;
use Illuminate\Support\Facades\Route;

Route::get('/health', function () {
    return response()->json([
        'status' => 'ok',
    ]);
});

Route::prefix('v1')->group(function (): void {
    Route::post('sessions', [SessionController::class, 'store']);

    Route::middleware('auth:sanctum')->group(function (): void {
        Route::get('session', [SessionController::class, 'show']);
        Route::delete('session', [SessionController::class, 'destroy']);

        Route::apiResource('orders', OrderController::class)
            ->only(['index', 'show']);
        Route::apiResource('orders.items', OrderItemController::class)
            ->only(['update']);
    });
});
