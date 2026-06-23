<?php

namespace App\Services;

use App\Repositories\Contracts\TransporterRepositoryInterface;

class TransporterService
{
    protected TransporterRepositoryInterface $transporterRepository;

    public function __construct(TransporterRepositoryInterface $transporterRepository)
    {
        $this->transporterRepository = $transporterRepository;
    }

    public function getAllTransporters()
    {
        return $this->transporterRepository->all();
    }

    public function getTransporterById($id)
    {
        return $this->transporterRepository->find($id);
    }

    public function createTransporter(array $data)
    {
        return $this->transporterRepository->create($data);
    }

    public function updateTransporter($id, array $data)
    {
        return $this->transporterRepository->update($id, $data);
    }

    public function deleteTransporter($id)
    {
        return $this->transporterRepository->delete($id);
    }
}
