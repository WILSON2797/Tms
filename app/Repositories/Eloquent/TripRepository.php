<?php

namespace App\Repositories\Eloquent;

use App\Models\Trip;
use App\Repositories\Contracts\TripRepositoryInterface;

class TripRepository extends BaseRepository implements TripRepositoryInterface
{
    public function __construct(Trip $model)
    {
        parent::__construct($model);
    }

    /**
     * Generate auto-incrementing trip number formatted as TRP-YYYYMM-XXXXX.
     */
    public function generateTripNumber(): string
    {
        $prefix = 'TRP-' . date('Ym') . '-';
        
        $lastTrip = $this->model->where('trip_number', 'like', $prefix . '%')
            ->orderBy('trip_number', 'desc')
            ->first();
            
        if ($lastTrip) {
            $lastNumber = intval(substr($lastTrip->trip_number, strlen($prefix)));
            $nextNumber = $lastNumber + 1;
        } else {
            $nextNumber = 1;
        }
        
        return $prefix . str_pad($nextNumber, 5, '0', STR_PAD_LEFT);
    }
}
