<?php

namespace App\V1\Http\Controllers\Notification\Setting;

use App\V1\Http\Controllers\ApiController;
use App\V1\Http\Resources\NotificationTemplateSettingClinicCollection;
use App\V1\Http\Resources\NotificationTemplateSettingClinicResource;
use App\V1\Contracts\Repositories\NotificationTemplateSettingClinicRepository;
use App\V1\Contracts\Repositories\Query\NotificationTemplateSettingClinicFilter;
use App\V1\Contracts\Repositories\Query\NotificationTemplateSettingClinicSorter;
use App\V1\Contracts\Repositories\Query\NotificationTemplateSettingClinicScopes;
use App\V1\Http\Requests\ListRequest;
use App\V1\Http\Requests\NotificationTemplateSettingClinicRequest;
use App\V1\Models\Notification\Setting\Clinic as SettingClinic;
use Illuminate\Http\Request;
class ClinicController extends ApiController
{
    /**
     * @var  NotificationTemplateSettingClinicRepository
     */
    protected $repository;

    /**
     * NotificationTemplateSettingClinicController constructor
     *
     * @param  NotificationTemplateSettingClinicRepository $repository
     */
    public function __construct(NotificationTemplateSettingClinicRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Get list of entities
     *
     * @param  ListRequest $request
     * @param  NotificationTemplateSettingClinicFilter $filter
     * @param  NotificationTemplateSettingClinicSorter $sorter
     * @param  NotificationTemplateSettingClinicScopes $scopes
     *
     * @return  NotificationTemplateSettingClinicCollection
     */
    public function list(ListRequest $request, NotificationTemplateSettingClinicFilter $filter,
        NotificationTemplateSettingClinicSorter $sorter, NotificationTemplateSettingClinicScopes $scopes) {
        $this->authorize('list', SettingClinic::class);
        $collection = $this->repository->paginate($filter, $sorter, $request->getPage(), $request->getLimit());
        $scopes->apply($collection);
        return new NotificationTemplateSettingClinicCollection($collection);
    }

    /**
     * Get requested entity
     *
     * @param  int $id
     * @param  NotificationTemplateSettingClinicScopes $scopes
     *
     * @return  NotificationTemplateSettingClinicResource
     */
    public function get($id, NotificationTemplateSettingClinicScopes $scopes)
    {
        $model = $this->repository->find($id);
        $this->authorize('get', $model);
        $scopes->apply($model);
        return new NotificationTemplateSettingClinicResource($model);
    }

    /**
     * Create new entity
     *
     * @param  NotificationTemplateSettingClinicRequest $request
     *
     * @return  NotificationTemplateSettingClinicResource
     */
    public function create(NotificationTemplateSettingClinicRequest $request)
    {
        $this->authorize('create', SettingClinic::class);
        $model = new SettingClinic($request->all());
        $this->repository->persist($model);
        return $this->respondCreated(new NotificationTemplateSettingClinicResource($model));
    }

    /**
     * Update the entity
     *
     * @param  NotificationTemplateSettingClinicRequest $request
     * @param  int $id
     *
     * @return  NotificationTemplateSettingClinicResource
     */
    public function update(NotificationTemplateSettingClinicRequest $request, $id)
    {
        $model = $this->repository->find($id);
        $this->authorize('update', $model);
        $model->fill($request->safe());
        $this->repository->persist($model);
        return $this->respondUpdated(new NotificationTemplateSettingClinicResource($model));
    }

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
        $this->repository->delete($model);
        return $this->respondDeleted();
    }
}
