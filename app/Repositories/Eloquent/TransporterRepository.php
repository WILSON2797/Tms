<?php

namespace App\Repositories\Eloquent;

use App\Models\Transporter;
use App\Repositories\Contracts\TransporterRepositoryInterface;

class TransporterRepository extends BaseRepository implements TransporterRepositoryInterface
{
    public function __construct(Transporter $model)
    {
        parent::__construct($model);
    }
}
