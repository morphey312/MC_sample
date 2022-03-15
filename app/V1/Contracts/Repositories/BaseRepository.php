<?php

namespace App\V1\Contracts\Repositories;

use Illuminate\Database\Eloquent\Model;
use App\V1\Contracts\Repositories\Query\Filter;
use App\V1\Contracts\Repositories\Query\Sorter;

interface BaseRepository
{
    const DEFAULT_PAGE_SIZE = 30;
    const RESULTS_MAX_NUMBER = 1000;
    
    /**
     * Find an entity by ID
     * 
     * @param int $id
     * @param bool $exceptionOnFail
     * 
     * @return Model
     */ 
    public function find($id, $exceptionOnFail = true);
    
    /**
     * Get all entities
     * 
     * @param Filter $filters
     * @param Sorter $order
     * @param int|bool $limit 
     * 
     * @return \Illuminate\Support\Collection
     */ 
    public function get(Filter $filters = null, Sorter $order = null, $limit = false);
    
    /**
     * Get number of entities
     * 
     * @param Filter $filters
     * 
     * @return int
     */ 
    public function count(Filter $filters = null);
    
    /**
     * Get entities by their ids
     * 
     * @param int|array $ids
     * 
     * @return \Illuminate\Support\Collection
     */
    public function getById($ids);
    
    /**
     * Get number of entities by their ids
     * 
     * @param int|array $ids
     * 
     * @return int
     */
    public function countById($ids);
    
    /**
     * Get number of entities by values
     * 
     * @param array $values array of values as follow ['attribute' => 'value', ['attribute', '!=', 'value'], ...]
     * @param Filter $filters additional filters
     * 
     * @return int
     */
    public function countByValues(array $values, Filter $filters = null);
    
    /**
     * Paginate entities
     * 
     * @param Filter $filters
     * @param Sorter $order
     * @param int $page
     * @param int|null $pageSize
     * @param bool $simplePager
     * 
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator|\Illuminate\Contracts\Pagination\Paginator
     */ 
    public function paginate(Filter $filters = null, Sorter $order = null, $page = 1, $pageSize = null, $simplePager = false);
    
    /**
     * Store model in database
     * 
     * @param Model $model
     * 
     * @return bool
     */ 
    public function persist(Model $model);
    
    /**
     * Delete model from database
     * 
     * @param Model $model
     * 
     * @return bool
     */ 
    public function delete(Model $model);
    
    /**
     * Create filter for the given repository
     * 
     * @param array $filters
     * 
     * @return Query\Filter
     */ 
    public function filter(array $filters = []);
    
    /**
     * Create sorter for the given repository
     * 
     * @param array $order
     * 
     * @return Query\Sorter
     */ 
    public function sorter(array $order = []);

    /**
     * @inherit
     */
    public function all(Filter $filters = null, Sorter $order = null);


    /**
     * Get the first record matching the attributes or create it.
     *
     * @param  array  $attributes
     * @param  array  $values
     * @return \Illuminate\Database\Eloquent\Model|static
     */
    public function firstOrCreate(array $attributes, array $values = []);

}
