<?php

namespace App\Services;

use App\Repositories\Contracts\DriverRepositoryInterface;
use Illuminate\Support\Facades\Storage;

class DriverService
{
    protected DriverRepositoryInterface $driverRepository;

    public function __construct(DriverRepositoryInterface $driverRepository)
    {
        $this->driverRepository = $driverRepository;
    }

    public function getAllDrivers()
    {
        return \App\Models\User::whereHas('role', function ($q) {
            $q->where('slug', 'driver');
        })->get();
    }

    public function getDriverById($id)
    {
        return $this->driverRepository->find($id);
    }

    public function createDriver(array $data)
    {
        if (isset($data['photo_file'])) {
            $path = $data['photo_file']->store('drivers', 'public');
            $data['photo'] = '/storage/' . $path;
        }
        unset($data['photo_file']);
        
        return $this->driverRepository->create($data);
    }

    public function updateDriver($id, array $data)
    {
        $driver = $this->getDriverById($id);
        
        if (isset($data['photo_file'])) {
            // Delete old photo if exists
            if ($driver->photo) {
                $oldPath = str_replace('/storage/', '', $driver->photo);
                Storage::disk('public')->delete($oldPath);
            }
            
            $path = $data['photo_file']->store('drivers', 'public');
            $data['photo'] = '/storage/' . $path;
        }
        unset($data['photo_file']);

        return $this->driverRepository->update($id, $data);
    }

    public function deleteDriver($id)
    {
        $driver = $this->getDriverById($id);
        if ($driver->photo) {
            $oldPath = str_replace('/storage/', '', $driver->photo);
            Storage::disk('public')->delete($oldPath);
        }
        return $this->driverRepository->delete($id);
    }
}
