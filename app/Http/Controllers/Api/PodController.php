<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\ShipmentOrderService;
use App\Http\Resources\ShipmentOrderResource;

class PodController extends Controller
{
    protected ShipmentOrderService $shipmentOrderService;

    public function __construct(ShipmentOrderService $shipmentOrderService)
    {
        $this->shipmentOrderService = $shipmentOrderService;
    }

    /**
     * Submit POD details for a Shipment Order.
     */
    public function submit(Request $request, $id)
    {
        $validated = $request->validate([
            'pod_recipient_name' => 'required|string|max:255',
            'pod_photo' => 'required|string', // Base64 data URL
            'pod_signature' => 'required|string', // Base64 data URL
        ]);

        try {
            $order = $this->shipmentOrderService->submitPod($id, $validated);
            return response()->json([
                'success' => true,
                'message' => 'Proof of Delivery (POD) berhasil disubmit',
                'data' => new ShipmentOrderResource($order->load(['customer', 'transporter', 'creator']))
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 422);
        }
    }
}
