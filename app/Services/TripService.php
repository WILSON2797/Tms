<?php

namespace App\Services;

use App\Repositories\Contracts\TripRepositoryInterface;
use App\Models\ShipmentOrder;
use App\Models\ShipmentStatusLog;
use Illuminate\Support\Facades\DB;

class TripService
{
    protected TripRepositoryInterface $tripRepository;

    public function __construct(TripRepositoryInterface $tripRepository)
    {
        $this->tripRepository = $tripRepository;
    }

    public function getAllTrips()
    {
        return $this->tripRepository->getModel()
            ->with(['driver', 'vehicle', 'transporter', 'creator', 'shipmentOrders', 'modeOfTransport', 'modeOfDelivery'])
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function getTripById($id)
    {
        return $this->tripRepository->getModel()
            ->with(['driver', 'vehicle', 'transporter', 'creator', 'shipmentOrders.customer', 'modeOfTransport', 'modeOfDelivery'])
            ->find($id);
    }

    public function createTrip(array $data, array $shipmentOrderIds)
    {
        return DB::transaction(function () use ($data, $shipmentOrderIds) {
            $data['trip_number'] = $this->tripRepository->generateTripNumber();
            $data['created_by'] = auth()->id() ?? 1;
            // Set status to ASSIGNED if driver is already allocated, otherwise DRAFT
            $data['status'] = !empty($data['driver_id']) ? 'ASSIGNED' : 'DRAFT';

            $trip = $this->tripRepository->create($data);

            if (!empty($shipmentOrderIds)) {
                $orderUpdate = [
                    'trip_id' => $trip->id,
                    'status' => 'ASSIGNED'
                ];
                if (!empty($trip->driver_id)) {
                    $orderUpdate['assigned_at'] = now();
                }
                ShipmentOrder::whereIn('id', $shipmentOrderIds)
                    ->update($orderUpdate);

                foreach ($shipmentOrderIds as $orderId) {
                    ShipmentStatusLog::create([
                        'shipment_order_id' => $orderId,
                        'status' => 'ASSIGNED',
                        'description' => 'Assigned to Trip ' . $trip->trip_number . '.',
                        'changed_by' => auth()->id() ?? 1
                    ]);
                }
            }

            return $trip->load(['driver', 'vehicle', 'transporter', 'shipmentOrders', 'modeOfTransport', 'modeOfDelivery']);
        });
    }

    public function updateTrip($id, array $data, ?array $shipmentOrderIds = null)
    {
        return DB::transaction(function () use ($id, $data, $shipmentOrderIds) {
            $trip = $this->tripRepository->find($id);
            if (!$trip) {
                throw new \Exception('Trip not found.');
            }

            // Update trip main data
            $this->tripRepository->update($id, $data);
            $trip = $trip->fresh();

            // Handle shipment orders updates if provided
            if ($shipmentOrderIds !== null) {
                $currentOrderIds = ShipmentOrder::where('trip_id', $trip->id)->pluck('id')->toArray();

                // Reset all currently linked orders first
                ShipmentOrder::where('trip_id', $trip->id)
                    ->update([
                        'trip_id' => null,
                        'status' => 'DRAFT'
                    ]);

                foreach ($currentOrderIds as $orderId) {
                    ShipmentStatusLog::create([
                        'shipment_order_id' => $orderId,
                        'status' => 'DRAFT',
                        'description' => 'Removed from Trip ' . $trip->trip_number . '.',
                        'changed_by' => auth()->id() ?? 1
                    ]);
                }

                // Link new set of orders
                if (!empty($shipmentOrderIds)) {
                    $orderStatus = 'ASSIGNED';
                    if ($trip->status === 'IN_TRANSIT') {
                        $orderStatus = 'IN_TRANSIT';
                    } elseif ($trip->status === 'DELIVERED') {
                        $orderStatus = 'DELIVERED';
                    }

                    $orderUpdate = [
                        'trip_id' => $trip->id,
                        'status' => $orderStatus
                    ];
                    if (!empty($trip->driver_id)) {
                        $orderUpdate['assigned_at'] = now();
                    }
                    if ($orderStatus === 'IN_TRANSIT') {
                        $orderUpdate['accepted_at'] = now();
                    } elseif ($orderStatus === 'DELIVERED') {
                        $orderUpdate['pod_received_at'] = now();
                    }

                    ShipmentOrder::whereIn('id', $shipmentOrderIds)
                        ->update($orderUpdate);

                    foreach ($shipmentOrderIds as $orderId) {
                        ShipmentStatusLog::create([
                            'shipment_order_id' => $orderId,
                            'status' => $orderStatus,
                            'description' => 'Assigned to Trip ' . $trip->trip_number . ' (Status: ' . $orderStatus . ').',
                            'changed_by' => auth()->id() ?? 1
                        ]);
                    }
                }
            } else {
                // If only status changed, sync it to existing orders
                $orderStatus = null;
                if ($trip->status === 'IN_TRANSIT') {
                    $orderStatus = 'IN_TRANSIT';
                } elseif ($trip->status === 'DELIVERED') {
                    $orderStatus = 'DELIVERED';
                } elseif ($trip->status === 'DRAFT') {
                    $orderStatus = 'ASSIGNED';
                } elseif ($trip->status === 'CANCELLED') {
                    $currentOrderIds = ShipmentOrder::where('trip_id', $trip->id)->pluck('id')->toArray();
                    
                    ShipmentOrder::where('trip_id', $trip->id)
                        ->update([
                            'trip_id' => null,
                            'status' => 'DRAFT'
                        ]);

                    foreach ($currentOrderIds as $orderId) {
                        ShipmentStatusLog::create([
                            'shipment_order_id' => $orderId,
                            'status' => 'DRAFT',
                            'description' => 'Removed from Trip ' . $trip->trip_number . ' due to Trip cancellation.',
                            'changed_by' => auth()->id() ?? 1
                        ]);
                    }
                }

                if ($orderStatus !== null && $trip->status !== 'CANCELLED') {
                    $currentOrderIds = ShipmentOrder::where('trip_id', $trip->id)->pluck('id')->toArray();
                    
                    $orderUpdate = ['status' => $orderStatus];
                    if ($orderStatus === 'IN_TRANSIT') {
                        $orderUpdate['accepted_at'] = now();
                    } elseif ($orderStatus === 'DELIVERED') {
                        $orderUpdate['pod_received_at'] = now();
                    }

                    ShipmentOrder::where('trip_id', $trip->id)
                        ->update($orderUpdate);

                    foreach ($currentOrderIds as $orderId) {
                        ShipmentStatusLog::create([
                            'shipment_order_id' => $orderId,
                            'status' => $orderStatus,
                            'description' => 'Status updated to ' . $orderStatus . ' via Trip ' . $trip->trip_number . '.',
                            'changed_by' => auth()->id() ?? 1
                        ]);
                    }
                }
            }

            return $trip->load(['driver', 'vehicle', 'transporter', 'shipmentOrders', 'modeOfTransport', 'modeOfDelivery']);
        });
    }

    public function deleteTrip($id)
    {
        return DB::transaction(function () use ($id) {
            $trip = $this->tripRepository->find($id);
            if (!$trip) {
                throw new \Exception('Trip not found.');
            }

            if ($trip->status !== 'DRAFT') {
                throw new \Exception('Only trips in DRAFT status can be deleted.');
            }

            $currentOrderIds = ShipmentOrder::where('trip_id', $trip->id)->pluck('id')->toArray();

            // Reset associated shipment orders
            ShipmentOrder::where('trip_id', $trip->id)
                ->update([
                    'trip_id' => null,
                    'status' => 'DRAFT'
                ]);

            foreach ($currentOrderIds as $orderId) {
                ShipmentStatusLog::create([
                    'shipment_order_id' => $orderId,
                    'status' => 'DRAFT',
                    'description' => 'Removed from Trip ' . $trip->trip_number . ' due to Trip deletion.',
                    'changed_by' => auth()->id() ?? 1
                ]);
            }

            return $this->tripRepository->delete($id);
        });
    }

    /**
     * Update current coordinates and log history.
     */
    public function updateLocation($id, array $coordinates)
    {
        return DB::transaction(function () use ($id, $coordinates) {
            $trip = $this->tripRepository->find($id);
            if (!$trip) {
                throw new \Exception('Trip not found.');
            }

            // Update live position on Trip
            $trip->update([
                'current_latitude' => $coordinates['latitude'],
                'current_longitude' => $coordinates['longitude']
            ]);

            // Create history log entry
            \App\Models\TripLocationLog::create([
                'trip_id' => $trip->id,
                'latitude' => $coordinates['latitude'],
                'longitude' => $coordinates['longitude']
            ]);

            return $trip->fresh();
        });
    }
}
