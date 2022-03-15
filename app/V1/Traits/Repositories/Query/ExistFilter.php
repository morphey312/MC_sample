<?php

namespace App\V1\Traits\Repositories\Query;

use App\V1\Repositories\AppointmentRepository;
use Illuminate\Support\Facades\App;

trait ExistFilter
{
    protected $repomap = [
        'appointments' => AppointmentRepository::class
    ];

    public function filterExists($query, $value)
    {
        $query->whereHas($value['rel'], function($query) use ($value) {
            $repo = App::make($this->repomap[$value['repo']]);
            $filter = $repo->filter($value['filters']);
            $filter->apply($query);
        });
    }
}
