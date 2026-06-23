<?php

namespace App\Repositories\Contracts;

interface TripRepositoryInterface extends BaseRepositoryInterface
{
    public function generateTripNumber(): string;
}
