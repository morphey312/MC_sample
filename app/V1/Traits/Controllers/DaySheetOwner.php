<?php

namespace App\V1\Traits\Controllers;

use App\V1\Http\Requests\ListRequest;
use App\V1\Http\Resources\OwnerDaySheetResource;

trait DaySheetOwner
{
    /**
     * Get short list of all employees
     *
     * @param EmployeeFilter $filter
     *
     * @return OwnerDaySheetResource
     */
    public function getDaySheets(ListRequest $request)
    {
        $model = $this->repository->find($request->input('filters.id'));
        $this->authorize('get', $model);
        $daySheets = $model->getDaySheets($request->input('filters.dates'), $request->input('filters.clinic'));
        return new OwnerDaySheetResource($daySheets);
    }
}
