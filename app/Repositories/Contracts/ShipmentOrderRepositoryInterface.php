<?php

namespace App\Repositories\Contracts;

interface ShipmentOrderRepositoryInterface extends BaseRepositoryInterface
{
    public function generateJobNumber(): string;
}
