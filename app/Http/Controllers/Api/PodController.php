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
        \Illuminate\Support\Facades\Log::info('POD Submit request for order ' . $id);
        \Illuminate\Support\Facades\Log::info('Recipient: ' . $request->input('pod_recipient_name'));
        \Illuminate\Support\Facades\Log::info('Photo length: ' . strlen($request->input('pod_photo', '')));
        \Illuminate\Support\Facades\Log::info('Signature length: ' . strlen($request->input('pod_signature', '')));

        try {
            $validated = $request->validate([
                'pod_recipient_name' => 'required|string|max:255',
                'pod_photo' => 'nullable|string', // Single photo (legacy)
                'pod_photos' => 'nullable|array', // Multiple photos
                'pod_signature' => 'required|string', // Base64 data URL
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            \Illuminate\Support\Facades\Log::error('POD Validation Failed: ' . json_encode($e->errors()));
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $e->errors()
            ], 422);
        }

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
