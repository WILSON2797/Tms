<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreShipmentOrderRequest;
use App\Http\Requests\UpdateShipmentOrderRequest;
use App\Http\Resources\ShipmentOrderResource;
use App\Services\ShipmentOrderService;

class ShipmentOrderController extends Controller
{
    protected ShipmentOrderService $shipmentOrderService;

    public function __construct(ShipmentOrderService $shipmentOrderService)
    {
        $this->shipmentOrderService = $shipmentOrderService;
    }

    public function index()
    {
        $orders = $this->shipmentOrderService->getAllOrders();
        return response()->json([
            'success' => true,
            'message' => 'Daftar shipment order berhasil diambil',
            'data' => ShipmentOrderResource::collection($orders)
        ]);
    }

    public function store(StoreShipmentOrderRequest $request)
    {
        $validated = $request->validated();

        $order = $this->shipmentOrderService->createOrder($validated);
        return response()->json([
            'success' => true,
            'message' => 'Shipment Order berhasil dibuat',
            'data' => new ShipmentOrderResource($order->load(['customer', 'transporter', 'creator']))
        ], 201);
    }

    public function show($id)
    {
        $order = $this->shipmentOrderService->getOrderById($id);
        if (!$order) {
            return response()->json([
                'success' => false,
                'message' => 'Shipment Order tidak ditemukan'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Detail shipment order berhasil diambil',
            'data' => new ShipmentOrderResource($order)
        ]);
    }

    public function update(UpdateShipmentOrderRequest $request, $id)
    {
        $validated = $request->validated();

        try {
            $order = $this->shipmentOrderService->updateOrder($id, $validated);
            return response()->json([
                'success' => true,
                'message' => 'Shipment Order berhasil diperbarui',
                'data' => new ShipmentOrderResource($order->load(['customer', 'transporter', 'creator']))
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
            $this->shipmentOrderService->deleteOrder($id);
            return response()->json([
                'success' => true,
                'message' => 'Shipment Order berhasil dihapus'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 422);
        }
    }

    public function markArrived($id)
    {
        try {
            $order = $this->shipmentOrderService->markAsArrived($id);
            return response()->json([
                'success' => true,
                'message' => 'Status order berhasil diperbarui menjadi ARRIVED',
                'data' => new ShipmentOrderResource($order)
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 422);
        }
    }
}
