<?php

namespace App\V1\Traits\Models;

use App\V1\Exceptions\ConstraintException;

trait HasConstraint
{
    /**
     * @inherit
     */ 
    protected static function bootHasConstraint()
    {
        static::deleting(function ($model) {
            $model->checkDeletingConstraints();
        });
    }

    /**
     * Check if there are relations on this record
     */ 
    public function checkDeletingConstraints()
    {
        if (property_exists($this, 'deleting_constraints')) {
            foreach ($this->deleting_constraints as $relation) {
                if ($this->{$relation}()->exists()) {
                    throw new ConstraintException('Model has constraints');
                }
            }
        }
    }
}