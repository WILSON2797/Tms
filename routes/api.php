<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CustomerController;
use App\Http\Controllers\Api\VehicleController;
use App\Http\Controllers\Api\TransporterController;
use App\Http\Controllers\Api\ShipmentOrderController;
use App\Http\Controllers\Api\TripController;

Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);
    Route::get('/dashboard/stats', [\App\Http\Controllers\Api\DashboardController::class, 'stats']);

    Route::get('/modes-of-transport', [\App\Http\Controllers\Api\ModeController::class, 'modesOfTransport']);
    Route::get('/modes-of-delivery', [\App\Http\Controllers\Api\ModeController::class, 'modesOfDelivery']);

    Route::get('/drivers', function () {
        $drivers = \App\Models\User::whereHas('role', function ($query) {
            $query->where('slug', 'driver');
        })->get()
        ->map(function ($user) {
            return [
                'id' => $user->id,
                'driver_name' => $user->name,
                'is_active' => true,
                'license_type' => null
            ];
        });
        return response()->json([
            'success' => true,
            'data' => $drivers
        ]);
    });

    // Master Data - View
    Route::middleware('permission:view-master')->group(function () {
        Route::get('/customers', [CustomerController::class, 'index']);
        Route::get('/customers/{customer}', [CustomerController::class, 'show']);
        Route::get('/vehicles', [VehicleController::class, 'index']);
        Route::get('/vehicles/{vehicle}', [VehicleController::class, 'show']);
        Route::get('/transporters', [TransporterController::class, 'index']);
        Route::get('/transporters/{transporter}', [TransporterController::class, 'show']);
        Route::get('/users', [\App\Http\Controllers\Api\UserController::class, 'index']);
        Route::get('/users/{id}', [\App\Http\Controllers\Api\UserController::class, 'show']);
        Route::get('/roles', function () {
            return response()->json([
                'success' => true,
                'data' => \App\Models\Role::orderBy('name')->get()
            ]);
        });
    });

    // Master Data - Create
    Route::middleware('permission:create-master')->group(function () {
        Route::post('/customers', [CustomerController::class, 'store']);
        Route::post('/vehicles', [VehicleController::class, 'store']);
        Route::post('/transporters', [TransporterController::class, 'store']);
        Route::post('/users', [\App\Http\Controllers\Api\UserController::class, 'store']);
    });

    // Master Data - Edit
    Route::middleware('permission:edit-master')->group(function () {
        Route::put('/customers/{customer}', [CustomerController::class, 'update']);
        Route::put('/vehicles/{vehicle}', [VehicleController::class, 'update']);
        Route::put('/transporters/{transporter}', [TransporterController::class, 'update']);
        Route::put('/users/{id}', [\App\Http\Controllers\Api\UserController::class, 'update']);
    });

    // Master Data - Delete
    Route::middleware('permission:delete-master')->group(function () {
        Route::delete('/customers/{customer}', [CustomerController::class, 'destroy']);
        Route::delete('/vehicles/{vehicle}', [VehicleController::class, 'destroy']);
        Route::delete('/transporters/{transporter}', [TransporterController::class, 'destroy']);
        Route::delete('/users/{id}', [\App\Http\Controllers\Api\UserController::class, 'destroy']);
    });

    // Shipment Orders - View
    Route::middleware('permission:view-shipment')->group(function () {
        Route::get('/shipment-orders', [ShipmentOrderController::class, 'index']);
        Route::get('/shipment-orders/unassigned', [TripController::class, 'unassignedOrders']);
        Route::get('/shipment-orders/{id}', [ShipmentOrderController::class, 'show']);
        
        // Trips - View
        Route::get('/trips', [TripController::class, 'index']);
        Route::get('/trips/{id}', [TripController::class, 'show']);
    });

    // Shipment Orders - Create
    Route::middleware('permission:create-shipment')->group(function () {
        Route::post('/shipment-orders', [ShipmentOrderController::class, 'store']);
        
        // Trips - Create
        Route::post('/trips', [TripController::class, 'store']);
    });

    // Shipment Orders - Edit
    Route::middleware('permission:edit-shipment')->group(function () {
        Route::put('/shipment-orders/{id}', [ShipmentOrderController::class, 'update']);
        
        // Trips - Edit
        Route::put('/trips/{id}', [TripController::class, 'update']);
    });

    // Shipment Orders - Delete
    Route::middleware('permission:delete-shipment')->group(function () {
        Route::delete('/shipment-orders/{id}', [ShipmentOrderController::class, 'destroy']);
        
        // Trips - Delete
        Route::delete('/trips/{id}', [TripController::class, 'destroy']);
    });

    // POD Submission
    Route::middleware('permission:upload-pod')->group(function () {
        Route::post('/shipment-orders/{id}/pod', [\App\Http\Controllers\Api\PodController::class, 'submit']);
    });

    // Location GPS Tracking Ping
    Route::post('/trips/{id}/location', [TripController::class, 'submitLocation']);
    
    // Arrived Destination confirmation
    Route::post('/shipment-orders/{id}/arrive', [ShipmentOrderController::class, 'markArrived']);
});
