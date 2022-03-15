<?php

namespace App\V1\Contracts\Repositories;

interface HandbookRepository extends BaseRepository
{
    /**
    * Paginate entities from the category
    *
    * @param string $category
    * @param array|null $filters
    * @param array|null $order
    * @param int $page
    * @param int|null $pageSize
    *
    * @return \Illuminate\Support\Collection
    */
    public function paginateCategory($category, $filters = null, $order = null, $page = 1, $pageSize = null);

    /**
     * Find city name by ID
     *
     * @param int $city
     * @param bool $checkAccess
     * @return mixed
     */
    public function findCity($city, $checkAccess = true);
}
