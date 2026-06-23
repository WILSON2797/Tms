<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreTripRequest;
use App\Http\Requests\UpdateTripRequest;
use App\Http\Resources\TripResource;
use App\Services\TripService;
use App\Models\ShipmentOrder;
use App\Http\Resources\ShipmentOrderResource;

class TripController extends Controller
{
    protected TripService $tripService;

    public function __construct(TripService $tripService)
    {
        $this->tripService = $tripService;
    }

    public function index()
    {
        $trips = $this->tripService->getAllTrips();
        return response()->json([
            'success' => true,
            'message' => 'Daftar trip berhasil diambil',
            'data' => TripResource::collection($trips)
        ]);
    }

    public function store(StoreTripRequest $request)
    {
        $validated = $request->validated();
        $orderIds = $validated['shipment_order_ids'] ?? [];
        unset($validated['shipment_order_ids']);

        $trip = $this->tripService->createTrip($validated, $orderIds);
        return response()->json([
            'success' => true,
            'message' => 'Trip berhasil dibuat',
            'data' => new TripResource($trip)
        ], 201);
    }

    public function show($id)
    {
        $trip = $this->tripService->getTripById($id);
        if (!$trip) {
            return response()->json([
                'success' => false,
                'message' => 'Trip tidak ditemukan'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Detail trip berhasil diambil',
            'data' => new TripResource($trip)
        ]);
    }

    public function update(UpdateTripRequest $request, $id)
    {
        $validated = $request->validated();
        $orderIds = $validated['shipment_order_ids'] ?? null;
        if (isset($validated['shipment_order_ids'])) {
            unset($validated['shipment_order_ids']);
        }

        try {
            $trip = $this->tripService->updateTrip($id, $validated, $orderIds);
            return response()->json([
                'success' => true,
                'message' => 'Trip berhasil diperbarui',
                'data' => new TripResource($trip)
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 422);
        }
    }

    public function destroy($id)
    {
        try {
            $this->tripService->deleteTrip($id);
            return response()->json([
                'success' => true,
                'message' => 'Trip berhasil dihapus'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 422);
        }
    }

    /**
     * Get shipment orders that are not assigned to any trip.
     */
    public function unassignedOrders()
    {
        $orders = ShipmentOrder::whereNull('trip_id')
            ->where('status', 'DRAFT')
            ->with(['customer', 'transporter'])
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'message' => 'Daftar shipment order unassigned berhasil diambil',
            'data' => ShipmentOrderResource::collection($orders)
        ]);
    }

    /**
     * Submit real-time GPS coordinate ping for a trip.
     */
    public function submitLocation(\Illuminate\Http\Request $request, $id)
    {
        $validated = $request->validate([
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
        ]);

        try {
            $trip = $this->tripService->updateLocation($id, $validated);
            return response()->json([
                'success' => true,
                'message' => 'Koordinat GPS supir berhasil direkam',
                'data' => new TripResource($trip)
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 422);
        }
    }
}
