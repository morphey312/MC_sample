<?php

namespace App\V1\Traits\Controllers\Patient;

use App\V1\Exceptions\ConstraintException;

trait HasConstraint
{
    /**
     * Check if entity has constraint
     * 
     * @param int $id
     * 
     * @return  \Illuminate\Http\JsonResponse
     */ 
    public function isDeleteable($id)
    {
        $model = $this->repository->find($id);
        $this->authorize('get', $model);

        try
        {
            $model->checkDeletingConstraints();
        } catch (ConstraintException $e) {
            return $this->respondError('This entity is in use', [], 423);
        }

        return $this->respondSuccess();
    }
}