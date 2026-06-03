<?php

use App\Http\Controllers\OrderItemStatusController;
use Illuminate\Support\Facades\Route;

Route::get('/health', function () {
    return response()->json([
        'status' => 'ok',
    ]);
});

Route::middleware('auth')->group(function (): void {
    Route::patch('order-item-status', [OrderItemStatusController::class, 'update']);
});
