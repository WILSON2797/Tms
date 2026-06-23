<?php

namespace App\Services;

use App\Repositories\Contracts\VehicleRepositoryInterface;

class VehicleService
{
    protected VehicleRepositoryInterface $vehicleRepository;

    public function __construct(VehicleRepositoryInterface $vehicleRepository)
    {
        $this->vehicleRepository = $vehicleRepository;
    }

    public function getAllVehicles()
    {
        return $this->vehicleRepository->all();
    }

    public function getVehicleById($id)
    {
        return $this->vehicleRepository->find($id);
    }

    public function createVehicle(array $data)
    {
        return $this->vehicleRepository->create($data);
    }

    public function updateVehicle($id, array $data)
    {
        return $this->vehicleRepository->update($id, $data);
    }

    public function deleteVehicle($id)
    {
        return $this->vehicleRepository->delete($id);
    }
}
