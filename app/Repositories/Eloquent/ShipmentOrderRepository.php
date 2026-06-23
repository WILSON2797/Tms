<?php

namespace App\Repositories\Eloquent;

use App\Models\ShipmentOrder;
use App\Repositories\Contracts\ShipmentOrderRepositoryInterface;

class ShipmentOrderRepository extends BaseRepository implements ShipmentOrderRepositoryInterface
{
    public function __construct(ShipmentOrder $model)
    {
        parent::__construct($model);
    }

    /**
     * Generate auto-incrementing job number formatted as JOB-YYYYMM-XXXXX.
     */
    public function generateJobNumber(): string
    {
        $prefix = 'JOB-' . date('Ym') . '-';
        
        $lastOrder = $this->model->where('job_number', 'like', $prefix . '%')
            ->orderBy('job_number', 'desc')
            ->first();
            
        if ($lastOrder) {
            $lastNumber = intval(substr($lastOrder->job_number, strlen($prefix)));
            $nextNumber = $lastNumber + 1;
        } else {
            $nextNumber = 1;
        }
        
        return $prefix . str_pad($nextNumber, 5, '0', STR_PAD_LEFT);
    }
}
