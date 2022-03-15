<?php

namespace App\V1\Traits\Controllers;

use App\V1\Exceptions\ConstraintException;

trait Deleteable 
{
    /**
     * Delete the entity
     *
     * @param  int $id
     * 
     * @return  \Illuminate\Http\JsonResponse
     */
    public function delete($id)
    {
        $model = $this->repository->find($id);
        $this->authorize('delete', $model);

        try {
            $this->repository->delete($model);
            return $this->respondDeleted();
        } catch (ConstraintException $e) {
            return $this->respondError('This entity is in use', [], 423);
        }
    }
}