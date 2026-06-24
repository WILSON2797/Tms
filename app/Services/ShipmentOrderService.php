<?php

namespace App\Services;

use App\Repositories\Contracts\ShipmentOrderRepositoryInterface;
use App\Models\ShipmentStatusLog;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ShipmentOrderService
{
    protected ShipmentOrderRepositoryInterface $shipmentOrderRepository;

    public function __construct(ShipmentOrderRepositoryInterface $shipmentOrderRepository)
    {
        $this->shipmentOrderRepository = $shipmentOrderRepository;
    }

    public function getAllOrders()
    {
        return $this->shipmentOrderRepository->getModel()
            ->with(['customer', 'transporter', 'creator', 'trip.driver'])
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function getOrderById($id)
    {
        return $this->shipmentOrderRepository->getModel()
            ->with(['customer', 'transporter', 'creator', 'statusLogs.changer', 'trip.driver', 'trip.vehicle', 'trip.locationLogs'])
            ->find($id);
    }

    public function createOrder(array $data)
    {
        return DB::transaction(function () use ($data) {
            $data['job_number'] = $this->shipmentOrderRepository->generateJobNumber();
            $data['created_by'] = auth()->id() ?? 1;
            $data['status'] = 'DRAFT';
            
            $order = $this->shipmentOrderRepository->create($data);

            ShipmentStatusLog::create([
                'shipment_order_id' => $order->id,
                'status' => 'DRAFT',
                'description' => 'Shipment Order created.',
                'changed_by' => auth()->id() ?? 1
            ]);

            return $order;
        });
    }

    public function updateOrder($id, array $data)
    {
        return DB::transaction(function () use ($id, $data) {
            $order = $this->shipmentOrderRepository->find($id);
            if (!$order) {
                throw new \Exception('Shipment Order not found.');
            }

            $oldStatus = $order->status;
            $this->shipmentOrderRepository->update($id, $data);
            $order = $order->fresh();

            if ($order->status !== $oldStatus) {
                ShipmentStatusLog::create([
                    'shipment_order_id' => $order->id,
                    'status' => $order->status,
                    'description' => 'Shipment Order status updated to ' . $order->status . '.',
                    'changed_by' => auth()->id() ?? 1
                ]);
            }

            return $order;
        });
    }

    public function deleteOrder($id)
    {
        $order = $this->shipmentOrderRepository->find($id);
        if (!$order) {
            throw new \Exception('Shipment Order not found.');
        }

        if ($order->status !== 'DRAFT') {
            throw new \Exception('Only Shipment Orders in DRAFT status can be deleted.');
        }

        return $this->shipmentOrderRepository->delete($id);
    }

    /**
     * Submit Proof of Delivery (POD) for shipment order.
     */
    public function submitPod($id, array $data)
    {
        return DB::transaction(function () use ($id, $data) {
            $order = $this->shipmentOrderRepository->find($id);
            if (!$order) {
                throw new \Exception('Shipment Order not found.');
            }

            if ($order->status !== 'IN_TRANSIT' && $order->status !== 'ASSIGNED' && $order->status !== 'ARRIVED') {
                throw new \Exception('Cannot submit POD for orders that are not in transit, assigned, or arrived.');
            }

            $photoPath = null;
            $sigPath = null;

            if (isset($data['pod_photo']) && $data['pod_photo']) {
                if ($data['pod_photo'] instanceof \Illuminate\Http\UploadedFile) {
                    $photoPath = $data['pod_photo']->store('pod_photos', 'public');
                } else {
                    $photoPath = $this->uploadBase64File($data['pod_photo'], 'pod_photos');
                }
            }

            if (isset($data['pod_signature']) && $data['pod_signature']) {
                $sigPath = $this->uploadBase64File($data['pod_signature'], 'pod_signatures');
            }

            $order->update([
                'status' => 'DELIVERED',
                'pod_recipient_name' => $data['pod_recipient_name'],
                'pod_photo_path' => $photoPath,
                'pod_signature_path' => $sigPath,
                'pod_received_at' => now(),
            ]);

            ShipmentStatusLog::create([
                'shipment_order_id' => $order->id,
                'status' => 'DELIVERED',
                'description' => 'Proof of Delivery submitted. Received by ' . $data['pod_recipient_name'] . '.',
                'changed_by' => auth()->id() ?? 1
            ]);

            return $order->fresh();
        });
    }

    /**
     * Mark shipment order as arrived.
     */
    public function markAsArrived($id)
    {
        return DB::transaction(function () use ($id) {
            $order = $this->shipmentOrderRepository->find($id);
            if (!$order) {
                throw new \Exception('Shipment Order not found.');
            }

            if ($order->status !== 'IN_TRANSIT') {
                throw new \Exception('Only orders in IN_TRANSIT status can be marked as ARRIVED.');
            }

            $order->update([
                'status' => 'ARRIVED',
                'arrived_at' => now(),
            ]);

            ShipmentStatusLog::create([
                'shipment_order_id' => $order->id,
                'status' => 'ARRIVED',
                'description' => 'Driver arrived at destination.',
                'changed_by' => auth()->id() ?? $order->trip->driver_id ?? 1
            ]);

            return $order->fresh();
        });
    }

    /**
     * Helper to decode and upload base64 images.
     */
    private function uploadBase64File(string $base64String, string $folder): string
    {
        if (preg_match('/^data:image\/(\w+);base64,/', $base64String, $type)) {
            $base64String = substr($base64String, strpos($base64String, ',') + 1);
            $type = strtolower($type[1]);

            if (!in_array($type, ['jpg', 'jpeg', 'gif', 'png'])) {
                throw new \Exception('Invalid image type: ' . $type);
            }
            $base64String = base64_decode($base64String);

            if ($base64String === false) {
                throw new \Exception('Base64 decode failed');
            }
        } else {
            throw new \Exception('Invalid Base64 string format');
        }

        $fileName = uniqid() . '.' . $type;
        $path = $folder . '/' . $fileName;
        Storage::disk('public')->put($path, $base64String);

        return $path;
    }
}
