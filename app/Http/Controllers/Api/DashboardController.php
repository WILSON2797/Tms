<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ShipmentOrder;
use Illuminate\Http\Request;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function stats()
    {
        $today = Carbon::today();
        
        $totalOrdersToday = ShipmentOrder::whereDate('created_at', $today)->count();
        $pendingOrders = ShipmentOrder::whereIn('status', ['DRAFT', 'ASSIGNED'])->count();
        $inTransit = ShipmentOrder::where('status', 'IN_TRANSIT')->count();
        $delivered = ShipmentOrder::where('status', 'DELIVERED')->count();

        return response()->json([
            'success' => true,
            'message' => 'Statistik dashboard berhasil diambil',
            'data' => [
                [
                    'title' => 'Total Orders Today',
                    'value' => (string) $totalOrdersToday,
                    'icon' => 'bi-box-seam',
                    'colorClass' => 'bg-primary-soft text-primary'
                ],
                [
                    'title' => 'Pending Orders',
                    'value' => (string) $pendingOrders,
                    'icon' => 'bi-clock-history',
                    'colorClass' => 'bg-warning-soft text-warning'
                ],
                [
                    'title' => 'In Transit',
                    'value' => (string) $inTransit,
                    'icon' => 'bi-truck',
                    'colorClass' => 'bg-info-soft text-info'
                ],
                [
                    'title' => 'Delivered',
                    'value' => (string) $delivered,
                    'icon' => 'bi-check-circle',
                    'colorClass' => 'bg-success-soft text-success'
                ]
            ]
        ]);
    }
}
