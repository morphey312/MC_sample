<?php

namespace App\V1\Http\Controllers;

use App\V1\Http\Controllers\ApiController;
use App\V1\Http\Resources\ExchangeRateCollection;
use App\V1\Contracts\Repositories\ExchangeRateRepository;
use App\V1\Contracts\Repositories\Query\ExchangeRateFilter;
use App\V1\Contracts\Repositories\Query\ExchangeRateSorter;
use App\V1\Contracts\Repositories\Query\ExchangeRateScopes;
use App\V1\Http\Requests\ListRequest;
use App\V1\Models\ExchangeRate;

class ExchangeRateController extends ApiController
{
    /**
     * @var  ExchangeRateRepository
     */
    protected $repository;
    
    /**
     * ExchangeRateController constructor
     *
     * @param  ExchangeRateRepository $repository
     */
    public function __construct(ExchangeRateRepository $repository)
    {
        $this->repository = $repository;
    }
    
    /**
     * Get list of entities
     *
     * @param  ListRequest $request
     * @param  ExchangeRateFilter $filter
     * @param  ExchangeRateSorter $sorter
     * @param  ExchangeRateScopes $scopes
     *
     * @return  ExchangeRateCollection
     */
    public function list(ListRequest $request, ExchangeRateFilter $filter, ExchangeRateSorter $sorter) {
        $this->authorize('list', ExchangeRate::class);
        $collection = $this->repository->paginate($filter, $sorter, $request->getPage(), $request->getLimit());
        return new ExchangeRateCollection($collection);
    }
}