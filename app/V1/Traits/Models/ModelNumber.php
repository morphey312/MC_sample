<?php

namespace App\V1\Traits\Models;

trait ModelNumber
{
    /**
     * Issue a number to the model
     */ 
    public function pickNumber()
    {
        $number = (int) static::max('number');
        
        if ($number > 0) {
            $this->number = $number + 1;
        } else {
            $this->number = 1;
        }
    }
}