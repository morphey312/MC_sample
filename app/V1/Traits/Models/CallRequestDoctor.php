<?php

namespace App\V1\Traits\Models;

use App\V1\Models\CallRequest;

trait CallRequestDoctor
{
    /**
     * Related day doctor appointments
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function call_requests()
    {
        return $this->morphMany(CallRequest::class, 'doctor');
    }
}