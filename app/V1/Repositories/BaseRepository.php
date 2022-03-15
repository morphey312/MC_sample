<?php

namespace App\V1\Repositories;

use App\V1\Contracts\Repositories\BaseRepository as BaseRepositoryInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use App\V1\Contracts\Repositories\Query\Filter;
use App\V1\Contracts\Repositories\Query\Sorter;
use App\V1\Contracts\Services\Permissions\SharedResource;
use Auth;
use App;

abstract class BaseRepository implements BaseRepositoryInterface
{
    /**
     * @var bool
     */
    protected $accessCheck = true;

    /**
     * @var bool
     */
    protected $skipAccessCheckOnce = false;

    /**
     * Create a query
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    abstract protected function query();

    /**
     * Skip access check
     *
     * @param bool|Closure $param If Closure - skip access check for the given function
     *      If true - skip access check until false is passed
     *      Otherwise skip access check for the next single query
     *
     * @return any
     */
    public function skipAccessCheck($param = null)
    {
        if ($param instanceof Closure) {
            $this->accessCheck = false;
            $this->skipAccessCheckOnce = false;
            $result = $param();
            $this->accessCheck = true;
            return $result;
        }
        if ($param === true) {
            $this->accessCheck = false;
            $this->skipAccessCheckOnce = false;
            return $this;
        }
        if ($param === false) {
            $this->accessCheck = true;
            $this->skipAccessCheckOnce = false;
            return $this;
        }
        $this->accessCheck = false;
        $this->skipAccessCheckOnce = true;
        return $this;
    }


    /**
     * Setup query builder
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param Filter $filters
     * @param Sorter $order
     * @param bool $checkAccess
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    protected function setupQuery($query, $filters, $order = null, $checkAccess = true)
    {
        $query->select($query->getModel()->getTable() . '.*');

        if ($filters !== null) {
            $filters->apply($query);
        }

        if ($order !== null) {
            $order->apply($query);
        }

        if ($checkAccess && $this->accessCheck) {
            if ($query->getModel() instanceof SharedResource) {
                $query->getModel()->applyAccessFilter($query, Auth::user());
            }
        } elseif ($this->skipAccessCheckOnce) {
            $this->skipAccessCheckOnce = false;
            $this->accessCheck = true;
        }


        return $query;
    }

    /**
     * @inherit
     */
    public function find($id, $exceptionOnFail = true)
    {
        if ($exceptionOnFail) {
            return $this->query()->findOrFail($id);
        }
        return $this->query()->find($id);
    }

    /**
     * Get the first record matching the attributes or create it.
     *
     * @param  array  $attributes
     * @param  array  $values
     * @return \Illuminate\Database\Eloquent\Model|static
     */
    public function firstOrCreate(array $attributes, array $values = [])
    {
        return $this->query()->firstOrCreate($attributes, $values = []);
    }

    /**
     * @inherit
     */
    public function get(Filter $filters = null, Sorter $order = null, $limit = false)
    {
        return $this->getInternal($this->query(), $filters, $order, $limit);
    }

    /**
     * Perform get query
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param Filter $filters
     * @param Sorter $order
     * @param int $limit
     *
     * @return \Illuminate\Support\Collection
     */
    protected function getInternal($query, $filters, $order, $limit)
    {
        return $this->setupQuery($query, $filters, $order)
            ->limit($limit ?: self::RESULTS_MAX_NUMBER)->get();
    }

    /**
     * @inherit
     */
    public function count(Filter $filters = null)
    {
        return $this->countInternal($this->query(), $filters);
    }

    /**
     * Perform count query
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param Filter $filters
     *
     * @return int
     */
    protected function countInternal($query, $filters)
    {
        return $this->setupQuery($query, $filters, null)->count();
    }

    /**
     * @inherit
     */
    public function getById($ids)
    {
        $query = $this->query()->whereKey($ids);
        return $this->getInternal($query, null, null, false);
    }

    /**
     * @inherit
     */
    public function countById($ids)
    {
        $query = $this->query()->whereKey($ids);
        return $this->countInternal($query, null);
    }

    /**
     * @inherit
     */
    public function countByValues(array $values, Filter $filters = null)
    {
        $query = $this->queryByValues($this->query(), $values);
        return $this->countInternal($query, $filters);
    }

    /**
     * @inherit
     */
    public function paginate(Filter $filters = null, Sorter $order = null, $page = 1, $pageSize = null, $simplePager = false)
    {
        return $this->paginateInternal($this->query(), $filters, $order, $page, $pageSize, $simplePager);
    }

    /**
     * Perform paginate query
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param Filter $filters
     * @param Sorter $order
     * @param int $page
     * @param int|null $pageSize
     * @param bool $simplePager
     *
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    protected function paginateInternal($query, $filters, $order, $page, $pageSize, $simplePager = false)
    {
        $query = $this->setupQuery($query, $filters, $order);

        return $simplePager
            ? $query->simplePaginate($pageSize ?: self::DEFAULT_PAGE_SIZE, ['*'], null, $page)
            : $query->paginate($pageSize ?: self::DEFAULT_PAGE_SIZE, ['*'], null, $page);
    }

    /**
     * @inherit
     */
    public function persist(Model $model)
    {
        return $model->save();
    }

    /**
     * @inherit
     */
    public function delete(Model $model)
    {
        return $model->delete();
    }

    /**
     * @inherit
     */
    public function all(Filter $filters = null, Sorter $order = null)
    {
        return $this->setupQuery($this->query(), $filters, $order)->get();
    }

    /**
     * Make filter instance
     *
     * @param string $class
     * @param array $filters
     *
     * @return Query\Filter
     */
    protected function makeFilter($class, $filters = [])
    {
        $instance = App::makeWith($class, ['request' => null]);
        $instance->setFilter($filters);
        return $instance;
    }

    /**
     * Make sorter instance
     *
     * @param string $class
     * @param array $order
     *
     * @return Query\Sorter
     */
    protected function makeSorter($class, $order = [])
    {
        $instance = App::makeWith($class, ['request' => null]);
        $instance->setOrder($order);
        return $instance;
    }

    /**
     * Apply custom criteria query
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param array $values
     *
     * @return int
     */
    protected function queryByValues($query, $values)
    {
        foreach ($values as $key => $value) {
            if (is_array($value)) {
                if (is_numeric($key)) {
                    $query->where($query->qualifyColumn($value[0]), $value[1], $value[2]);
                } else {
                    $query->whereIn($query->qualifyColumn($key), $value);
                }
            } else {
                $query->where($query->qualifyColumn($key), $value);
            }
        }
        return $query;
    }

    /**
     * Perform get first
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param Filter $filters
     * @param Sorter $order
     *
     * @return \Illuminate\Support\Collection
     */
    public function first(Filter $filters = null, Sorter $order = null)
    {
        return $this->setupQuery($this->query(), $filters, $order)->first();
    }
}
