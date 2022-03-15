<?php

namespace App\V1\Contracts\Repositories;

use App\V1\Contracts\Repositories\BaseRepository;

interface DaySheetRepository extends BaseRepository
{
    /**
     * Get day sheets for separate filters
     * 
     * @param array $filters
     * @param array $order
     * 
     * @return collection
     */ 
    public function getDaySheets($filters, $order);

    /**
     * Get single employee day sheet
     * 
     * @param array $filter
     * 
     * @return mixed
     */ 
    public function getSingleDaySheet($filter);

    /**
     * Get count day sheets in period group by clinic
     * 
     * @param DaySheetFilter $filter
     * 
     * @return collection
     */
    public function getCount($filter = null);

    /**
     * Delete day sheets list
     * 
     * @param array $ids
     */
    public function deleteList($ids = []);

    /**
     * Get available day sheets
     * 
     * @param DaySheetFilter $filter
     * @param array $params
     * 
     * @return mixed
     */
    public function getAvailableDaySheets($filter = null, $params = []);
    
    /**
     * Get day sheets surgery appointments
     * 
     * @param array $skipId
     * @param array $filters
     * 
     * @return collection
     */ 
    public function getDoctorSurgerySheets($skipId = [], $filters);
}