<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\MachineController;
use App\Http\Controllers\Api\ProductInventoryController;
use App\Http\Controllers\Api\MachineSlotController;
use App\Http\Controllers\Api\MachineStatusLogController;
use App\Http\Controllers\Api\TelemetryDataController;
use App\Http\Controllers\Api\MaintenanceLogController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// =========================
// ORDER API
// =========================

Route::get('/orders', [OrderController::class, 'index']);
Route::post('/orders', [OrderController::class, 'store']);
Route::get('/orders/{order}', [OrderController::class, 'show']);
Route::put('/orders/{order}', [OrderController::class, 'update']);

Route::post('/orders/{order}/items', [OrderController::class, 'addItem']);

Route::get('/orders/{order}/payments', [OrderController::class, 'payments']);
Route::post('/orders/{order}/payments', [OrderController::class, 'addPayment']);


// =========================
// PRODUCT API
// =========================

Route::get('/products', [ProductController::class, 'index']);
Route::post('/products', [ProductController::class, 'store']);
Route::get('/products/{product}', [ProductController::class, 'show']);
Route::put('/products/{product}', [ProductController::class, 'update']);
Route::delete('/products/{product}', [ProductController::class, 'destroy']);

// =========================
// MACHINE API
// =========================

Route::get('/machines', [MachineController::class, 'index']);
Route::post('/machines', [MachineController::class, 'store']);
Route::get('/machines/{machine}', [MachineController::class, 'show']);
Route::put('/machines/{machine}', [MachineController::class, 'update']);
Route::delete('/machines/{machine}', [MachineController::class, 'destroy']);

// =========================
// INVENTORY API
// =========================

Route::get('/inventories', [ProductInventoryController::class, 'index']);
Route::post('/inventories', [ProductInventoryController::class, 'store']);
Route::get('/inventories/{productInventory}', [ProductInventoryController::class, 'show']);
Route::put('/inventories/{productInventory}', [ProductInventoryController::class, 'update']);
Route::delete('/inventories/{productInventory}', [ProductInventoryController::class, 'destroy']);

// =========================
// MACHINE SLOT API
// =========================

Route::get('/machine-slots', [MachineSlotController::class, 'index']);
Route::post('/machine-slots', [MachineSlotController::class, 'store']);
Route::get('/machine-slots/{machineSlot}', [MachineSlotController::class, 'show']);
Route::put('/machine-slots/{machineSlot}', [MachineSlotController::class, 'update']);
Route::delete('/machine-slots/{machineSlot}', [MachineSlotController::class, 'destroy']);

// =========================
// MACHINE STATUS LOG API
// =========================

Route::get('/machine-status-logs', [MachineStatusLogController::class, 'index']);
Route::post('/machine-status-logs', [MachineStatusLogController::class, 'store']);
Route::get('/machine-status-logs/{machineStatusLog}', [MachineStatusLogController::class, 'show']);
Route::put('/machine-status-logs/{machineStatusLog}', [MachineStatusLogController::class, 'update']);
Route::delete('/machine-status-logs/{machineStatusLog}', [MachineStatusLogController::class, 'destroy']);

// =========================
// TELEMETRY API
// =========================

Route::get('/telemetry', [TelemetryDataController::class, 'index']);
Route::post('/telemetry', [TelemetryDataController::class, 'store']);
Route::get('/telemetry/{telemetryData}', [TelemetryDataController::class, 'show']);
Route::put('/telemetry/{telemetryData}', [TelemetryDataController::class, 'update']);
Route::delete('/telemetry/{telemetryData}', [TelemetryDataController::class, 'destroy']);

// =========================
// MAINTENANCE LOG API
// =========================

Route::get('/maintenance-logs', [MaintenanceLogController::class, 'index']);
Route::post('/maintenance-logs', [MaintenanceLogController::class, 'store']);
Route::get('/maintenance-logs/{maintenanceLog}', [MaintenanceLogController::class, 'show']);
Route::put('/maintenance-logs/{maintenanceLog}', [MaintenanceLogController::class, 'update']);
Route::delete('/maintenance-logs/{maintenanceLog}', [MaintenanceLogController::class, 'destroy']);