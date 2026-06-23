<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Customer;
use App\Models\Driver;
use App\Models\Transporter;
use App\Models\Vehicle;
use App\Models\ModeOfTransport;
use App\Models\ModeOfDelivery;
use App\Models\ShipmentOrder;
use App\Models\User;
use App\Models\Trip;
use App\Models\TripLocationLog;
use App\Models\ShipmentStatusLog;

class MasterDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Seed Customers
        $customers = [
            [
                'customer_code' => 'CUST-001',
                'customer_name' => 'PT Indofood CBP Sukses Makmur',
                'address' => 'Jl. Sudirman Kav 21',
                'city' => 'Jakarta Selatan',
                'pic_name' => 'Budi Santoso',
                'phone' => '081234567890',
                'email' => 'budi.santoso@indofood.com',
                'is_active' => true,
            ],
            [
                'customer_code' => 'CUST-002',
                'customer_name' => 'PT Unilever Indonesia Tbk',
                'address' => 'BSD Green Office Park Kav 3',
                'city' => 'Tangerang',
                'pic_name' => 'Siti Aminah',
                'phone' => '081398765432',
                'email' => 'siti.aminah@unilever.com',
                'is_active' => true,
            ],
            [
                'customer_code' => 'CUST-003',
                'customer_name' => 'PT Toyota Astra Motor',
                'address' => 'Jl. Laksda Yos Sudarso Sunter II',
                'city' => 'Jakarta Utara',
                'pic_name' => 'Hendra Wijaya',
                'phone' => '081122334455',
                'email' => 'hendra.wijaya@toyota.co.id',
                'is_active' => true,
            ],
        ];

        foreach ($customers as $cust) {
            Customer::updateOrCreate(['customer_code' => $cust['customer_code']], $cust);
        }

        // 2. Seed Drivers
        $drivers = [
            [
                'driver_code' => 'DRV-001',
                'driver_name' => 'Ahmad Subarjo',
                'phone' => '082155551111',
                'sim_number' => '950812345678',
                'sim_expired' => '2029-12-31',
                'license_type' => 'BII Umum',
                'address' => 'Kampung Melayu RT 05/02',
                'is_active' => true,
            ],
            [
                'driver_code' => 'DRV-002',
                'driver_name' => 'Joko Susilo',
                'phone' => '082155552222',
                'sim_number' => '940312345678',
                'sim_expired' => '2028-06-15',
                'license_type' => 'BI Umum',
                'address' => 'Senen Jaya Barat No. 12',
                'is_active' => true,
            ],
            [
                'driver_code' => 'DRV-003',
                'driver_name' => 'Rian Hidayat',
                'phone' => '082155553333',
                'sim_number' => '960712345678',
                'sim_expired' => '2027-08-20',
                'license_type' => 'A',
                'address' => 'Cengkareng Timur Indah',
                'is_active' => true,
            ],
        ];

        foreach ($drivers as $driver) {
            Driver::updateOrCreate(['driver_code' => $driver['driver_code']], $driver);
        }

        // 3. Seed Transporters
        $transporters = [
            [
                'transporter_code' => 'TRSP-001',
                'transporter_name' => 'PT Dunex Logistics',
                'address' => 'Jl. Danau Sunter Barat No 12',
                'pic_name' => 'Rudi Hermawan',
                'phone' => '0216501234',
                'email' => 'rudi.h@dunex.co.id',
                'is_active' => true,
            ],
            [
                'transporter_code' => 'TRSP-002',
                'transporter_name' => 'PT Lookman Djaja',
                'address' => 'Jl. Margomulyo No 22',
                'pic_name' => 'Kyoto Lookman',
                'phone' => '0317491234',
                'email' => 'info@lookmandjaja.com',
                'is_active' => true,
            ],
        ];

        foreach ($transporters as $trsp) {
            Transporter::updateOrCreate(['transporter_code' => $trsp['transporter_code']], $trsp);
        }

        // 4. Seed Vehicles
        $vehicles = [
            [
                'vehicle_no' => 'B 9012 TXS',
                'vehicle_type' => 'Wingbox',
                'brand' => 'Hino',
                'model' => 'Ranger 500',
                'capacity_weight' => 18000.00,
                'capacity_volume' => 45.00,
                'ownership' => 'internal',
                'is_active' => true,
            ],
            [
                'vehicle_no' => 'B 9234 CDD',
                'vehicle_type' => 'CDD Box',
                'brand' => 'Isuzu',
                'model' => 'Giga FRR',
                'capacity_weight' => 8000.00,
                'capacity_volume' => 24.00,
                'ownership' => 'internal',
                'is_active' => true,
            ],
            [
                'vehicle_no' => 'L 8765 CDE',
                'vehicle_type' => 'CDE Box',
                'brand' => 'Mitsubishi',
                'model' => 'Colt Diesel FE 71',
                'capacity_weight' => 4000.00,
                'capacity_volume' => 9.00,
                'ownership' => 'external',
                'is_active' => true,
            ],
        ];

        foreach ($vehicles as $veh) {
            Vehicle::updateOrCreate(['vehicle_no' => $veh['vehicle_no']], $veh);
        }

        // 5. Seed Mode of Transport (MOT)
        $mots = [
            ['code' => 'SELF_DRIVE', 'name' => 'Self Drive', 'is_active' => true],
            ['code' => 'FTL', 'name' => 'Full Truck Load (FTL)', 'is_active' => true],
            ['code' => 'LTL', 'name' => 'Less Truck Load (LTL)', 'is_active' => true],
            ['code' => 'FCL', 'name' => 'Full Container Load (FCL)', 'is_active' => true],
            ['code' => 'LCL', 'name' => 'Less Container Load (LCL)', 'is_active' => true],
        ];

        foreach ($mots as $mot) {
            ModeOfTransport::updateOrCreate(['code' => $mot['code']], $mot);
        }

        // 6. Seed Mode of Delivery (MOD)
        $mods = [
            ['code' => 'DTD', 'name' => 'Door to Door (DTD)', 'is_active' => true],
            ['code' => 'DTD_DEALER', 'name' => 'Door to Dealer', 'is_active' => true],
            ['code' => 'DTP', 'name' => 'Door to Port (DTP)', 'is_active' => true],
            ['code' => 'PTD', 'name' => 'Port to Door (PTD)', 'is_active' => true],
            ['code' => 'PTP', 'name' => 'Port to Port (PTP)', 'is_active' => true],
        ];

        foreach ($mods as $mod) {
            ModeOfDelivery::updateOrCreate(['code' => $mod['code']], $mod);
        }

        // 7. Seed Sample Shipment Orders
        $user = User::first() ?? User::create([
            'name' => 'Demo User',
            'username' => 'demo',
            'email' => 'demo@tms.com',
            'password' => bcrypt('password'),
        ]);

        $orders = [
            [
                'job_number' => 'JOB-202606-00001',
                'order_number' => 'PO-88127391',
                'customer_id' => 3, // Toyota Astra Motor
                'order_date' => '2026-06-23',
                'origin_city' => 'Karawang',
                'destination_city' => 'Jakarta Utara',
                'detail_address' => 'Sunter II, Jl. Gaya Motor III No. 5',
                'transporter_id' => 1,
                'recipient_name' => 'Arie Setiawan',
                'recipient_company' => 'Toyota Dealer Sunter',
                'expected_delivery_date' => '2026-06-24',
                'order_type' => 'URGENT',
                'status' => 'DRAFT',
                'created_by' => $user->id,
            ],
            [
                'job_number' => 'JOB-202606-00002',
                'order_number' => 'PO-88127392',
                'customer_id' => 3, // Toyota Astra Motor
                'order_date' => '2026-06-23',
                'origin_city' => 'Karawang',
                'destination_city' => 'Tangerang',
                'detail_address' => 'BSD City, Jl. Boulevard Raya No. 12',
                'transporter_id' => 1,
                'recipient_name' => 'Dewi Lestari',
                'recipient_company' => 'Toyota Dealer BSD',
                'expected_delivery_date' => '2026-06-25',
                'order_type' => 'REGULAR',
                'status' => 'DRAFT',
                'created_by' => $user->id,
            ],
            [
                'job_number' => 'JOB-202606-00003',
                'order_number' => 'PO-88127393',
                'customer_id' => 1, // PT Indofood
                'order_date' => '2026-06-23',
                'origin_city' => 'Karawang',
                'destination_city' => 'Bandung',
                'detail_address' => 'Jl. Asia Afrika No. 100',
                'transporter_id' => null,
                'recipient_name' => 'Eko Prasetyo',
                'recipient_company' => 'Indofood Hub Bandung',
                'expected_delivery_date' => '2026-06-26',
                'order_type' => 'REGULAR',
                'status' => 'DRAFT',
                'created_by' => $user->id,
            ],
        ];

        foreach ($orders as $order) {
            ShipmentOrder::updateOrCreate(['job_number' => $order['job_number']], $order);
        }

        // 8. Seed Sample Trips, Status Logs & GPS Location Logs
        $mot = ModeOfTransport::where('code', 'FTL')->first();
        $mod = ModeOfDelivery::where('code', 'DTD')->first();
        $driver = User::whereHas('role', function ($q) {
            $q->where('slug', 'driver');
        })->where('name', 'Ahmad Subarjo')->first();
        $vehicle = Vehicle::where('vehicle_no', 'B 9012 TXS')->first();
        $transporter = Transporter::where('transporter_code', 'TRSP-001')->first();

        if ($mot && $mod && $driver && $vehicle && $transporter) {
            // Trip 1: IN_TRANSIT with GPS location logs (Karawang to Tangerang)
            $trip1 = Trip::create([
                'trip_number' => 'TRIP-202606-00001',
                'trip_date' => '2026-06-23',
                'mot_id' => $mot->id,
                'mod_id' => $mod->id,
                'transporter_id' => $transporter->id,
                'driver_id' => $driver->id,
                'vehicle_id' => $vehicle->id,
                'status' => 'IN_TRANSIT',
                'current_latitude' => -6.2088,
                'current_longitude' => 106.8456,
                'created_by' => $user->id,
            ]);

            $order2 = ShipmentOrder::where('order_number', 'PO-88127392')->first();
            if ($order2) {
                $order2->update([
                    'trip_id' => $trip1->id,
                    'status' => 'IN_TRANSIT',
                ]);

                // Create Status Logs
                ShipmentStatusLog::create([
                    'shipment_order_id' => $order2->id,
                    'status' => 'DRAFT',
                    'description' => 'Shipment order created in system.',
                    'changed_by' => $user->id,
                    'created_at' => now()->subHours(5),
                ]);

                ShipmentStatusLog::create([
                    'shipment_order_id' => $order2->id,
                    'status' => 'ASSIGNED',
                    'description' => 'Driver ' . $driver->name . ' and vehicle ' . $vehicle->vehicle_no . ' assigned to trip ' . $trip1->trip_number,
                    'changed_by' => $user->id,
                    'created_at' => now()->subHours(3),
                ]);

                ShipmentStatusLog::create([
                    'shipment_order_id' => $order2->id,
                    'status' => 'IN_TRANSIT',
                    'description' => 'Shipment departed from Karawang Factory, heading to Tangerang.',
                    'changed_by' => $user->id,
                    'created_at' => now()->subHours(1),
                ]);

                // Trip location logs
                $logs = [
                    ['latitude' => -6.3073, 'longitude' => 107.2913, 'created_at' => now()->subMinutes(120)], // Karawang (Origin)
                    ['latitude' => -6.2855, 'longitude' => 107.1698, 'created_at' => now()->subMinutes(90)],  // Cikarang
                    ['latitude' => -6.2383, 'longitude' => 106.9756, 'created_at' => now()->subMinutes(60)],  // Bekasi
                    ['latitude' => -6.2088, 'longitude' => 106.8456, 'created_at' => now()->subMinutes(30)],  // Jakarta (Current)
                ];

                foreach ($logs as $log) {
                    TripLocationLog::create([
                        'trip_id' => $trip1->id,
                        'latitude' => $log['latitude'],
                        'longitude' => $log['longitude'],
                        'created_at' => $log['created_at'],
                    ]);
                }
            }

            // Trip 2: ASSIGNED with NO GPS location logs yet (Karawang to Jakarta Utara)
            // This tests the fallback: truck icon should be placed at the Origin
            $trip2 = Trip::create([
                'trip_number' => 'TRIP-202606-00002',
                'trip_date' => '2026-06-23',
                'mot_id' => $mot->id,
                'mod_id' => $mod->id,
                'transporter_id' => $transporter->id,
                'driver_id' => $driver->id,
                'vehicle_id' => $vehicle->id,
                'status' => 'ASSIGNED',
                'current_latitude' => null,
                'current_longitude' => null,
                'created_by' => $user->id,
            ]);

            $order1 = ShipmentOrder::where('order_number', 'PO-88127391')->first();
            if ($order1) {
                $order1->update([
                    'trip_id' => $trip2->id,
                    'status' => 'ASSIGNED',
                ]);

                ShipmentStatusLog::create([
                    'shipment_order_id' => $order1->id,
                    'status' => 'DRAFT',
                    'description' => 'Shipment order created in system.',
                    'changed_by' => $user->id,
                    'created_at' => now()->subHours(2),
                ]);

                ShipmentStatusLog::create([
                    'shipment_order_id' => $order1->id,
                    'status' => 'ASSIGNED',
                    'description' => 'Driver ' . $driver->name . ' and vehicle ' . $vehicle->vehicle_no . ' assigned to trip ' . $trip2->trip_number,
                    'changed_by' => $user->id,
                    'created_at' => now()->subMinutes(30),
                ]);
            }
        }
    }
}
