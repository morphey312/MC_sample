<?php

namespace App\V1\Http\Controllers;

use Illuminate\Support\Facades\App;
use App\V1\Http\Requests\ListRequest;
use App\V1\Exceptions\ConstraintException;

class BaseResourceController extends ApiController
{
    /**
     * @var string
     */
    protected $modelClass = null;

    /**
     * @var string
     */
    protected $repositoryClass = null;

    /**
     * @var string
     */
    protected $filterClass = null;

    /**
     * @var string
     */
    protected $sorterClass = null;

    /**
     * @var string
     */
    protected $requestClass = null;

    /**
     * @var string
     */
    protected $updateRequestClass = null;

    /**
     * @var string
     */
    protected $resourceClass = null;

    /**
     * @var mixed
     */
    protected $repository;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->repository = App::make($this->repositoryClass);
    }

    /**
     * Get list of entities
     *
     * @param  ListRequest $request
     *
     * @return mixed
     */
    public function list(ListRequest $request)
    {
        $filter = $this->filterClass ? App::make($this->filterClass) : null;
        $this->authorize('list', [$this->modelClass, $filter]);
        $sorter = $this->sorterClass ? App::make($this->sorterClass) : null;
        $simplePager = $request->input('simple_pager') === 'true';
        $collection = $this->repository->paginate($filter, $sorter, $request->getPage(), $request->getLimit(), $simplePager);
        return $this->makeResource($collection);
    }

    /**
     * Get requested entity
     *
     * @param  int $id
     *
     * @return  mixed
     */
    public function get($id)
    {
        $model = $this->repository->find($id);
        $this->authorize('get', $model);
        return $this->makeResource($model);
    }

    /**
     * Create new entity
     *
     * @return  mixed
     */
    public function create()
    {
        $this->authorize('create', $this->modelClass);
        $request = App::make($this->requestClass);
        $request->validateResolved();
        $model = App::make($this->modelClass);
        $prepared = $this->prepareForCreate($model, $request);
        if ($prepared === true) {
            $this->repository->persist($model);
            return $this->completeCreate($model, $request);
        } else {
            return $prepared;
        }
    }

    /**
     * Update the entity
     *
     * @param  int $id
     *
     * @return mixed
     */
    public function update($id)
    {
        $model = $this->repository->find($id);
        $this->authorize('update', $model);
        $request = App::make($this->updateRequestClass ?: $this->requestClass);
        $request->validateResolved();
        $prepared = $this->prepareForUpdate($model, $request);
        if ($prepared === true) {
            $this->repository->persist($model);
            return $this->completeUpdate($model, $request);
        } else {
            return $prepared;
        }
    }

    /**
     * Delete the entity
     *
     * @param  int $id
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function delete($id)
    {
        $model = $this->repository->find($id);
        $this->authorize('delete', $model);
        try {
            $prepared = $this->prepareForDelete($model);
            if ($prepared === true) {
                $this->repository->delete($model);
                return $this->completeDelete($model);
            } else {
                return $prepared;
            }
        } catch (ConstraintException $e) {
            return $this->respondError('This entity is in use', [], 423);
        }
    }

    /**
     * Get short list of all entities
     *
     * @param ListRequest $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function all(ListRequest $request)
    {
        $filter = $this->filterClass ? App::make($this->filterClass) : null;
        $this->authorize('all', [$this->modelClass, $filter]);
        $sorter = $this->sorterClass ? App::make($this->sorterClass) : null;
        $collection = $this->repository->get($filter, $sorter, $request->getLimit());
        return $this->respondSuccess($this->makeResource($collection)->toList());
    }

    /**
     * Make resource instance
     *
     * @param mixed $data
     *
     * @return mixed
     */
    protected function makeResource($data)
    {
        $reflection = new \ReflectionClass($this->resourceClass);
        return $reflection->newInstance($data);
    }

    /**
     * Prepare model to be created
     *
     * @param \App\V1\Models\BaseModel $model
     * @param \Illuminate\Http\Request $request
     *
     * @return mixed
     */
    protected function prepareForCreate($model, $request)
    {
        $model->fill($request->safe());
        return true;
    }

    /**
     * Prepare model to be updated
     *
     * @param \App\V1\Models\BaseModel $model
     * @param \Illuminate\Http\Request $request
     *
     * @return mixed
     */
    protected function prepareForUpdate($model, $request)
    {
        $model->fill($request->safe());
        return true;
    }

    /**
     * Prepare model to be deleted
     *
     * @param \App\V1\Models\BaseModel $model
     *
     * @return mixed
     */
    protected function prepareForDelete($model)
    {
        return true;
    }

    /**
     * Complete create model process
     *
     * @param \App\V1\Models\BaseModel $model
     * @param \Illuminate\Http\Request $request
     *
     * @return mixed
     */
    protected function completeCreate($model, $request)
    {
        event(sprintf('resource.created: %s', $this->modelClass), [$model, $request]);
        return $this->respondCreated($this->makeResource($model));
    }

    /**
     * Complete update model process
     *
     * @param \App\V1\Models\BaseModel $model
     * @param \Illuminate\Http\Request $request
     *
     * @return mixed
     */
    protected function completeUpdate($model, $request)
    {
        event(sprintf('resource.updated: %s', $this->modelClass), [$model, $request]);
        return $this->respondUpdated($this->makeResource($model));
    }

    /**
     * Complete delete model process
     *
     * @param \App\V1\Models\BaseModel $model
     *
     * @return mixed
     */
    protected function completeDelete($model)
    {
        event(sprintf('resource.deleted: %s', $this->modelClass), $model);
        return $this->respondDeleted();
    }
}
