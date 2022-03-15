<?php

namespace App\V1\Traits\Models;

use App\V1\Models\DaySheet;

trait DaySheetOwner
{
    /**
     * Related day sheets
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function day_sheets()
    {
        return $this->morphMany(DaySheet::class, 'day_sheet_owner');
    }

    /**
     * Get filtered related day sheets by clinic and date
     * 
     * @param $date
     * @param int $clinic 
     */ 
    public function getDaySheetByClinicDate($date, $clinic)
    {
        return $this->day_sheets()
                    ->where([
                        ['date', '=', $date],
                        ['clinic_id', '=', $clinic]
                    ])
                    ->first();
    }

    /**
     * Get filtered related day sheets by dates and clinic
     * 
     * @param array $dates
     * @param int $clinic
     * 
     * @return collection
     */ 
    public function getDaySheets($dates, $clinic = null)
    {
        return $this->getDaySheetsQuery($dates, $clinic)->get();
    }

    /**
     * Get query by dates and clinic
     * 
     * @param array $dates
     * @param int $clinic
     * 
     * @return $query
     */ 
    protected function getDaySheetsQuery($dates = [], $clinic = null)
    {
        $query = $this->day_sheets()
                    ->where(function ($query) use ($dates) {
                        foreach($dates as $date) {
                            $query->orWhere('date', '=', $date);
                        }
                    });

        if ($clinic) {
            $query->where('clinic_id', '=', $clinic);
        }
        return $query;
    }

    /**
     * Get filtered time sheets by clinic, date, related day sheets
     * 
     * @param $date
     * @param int $clinic
     */ 
    public function getTimeSheetsByClinicDate($date, $clinic)
    {
        $daySheet = $this->getDaySheetByClinicDate($date, $clinic);
        return  $daySheet ? $daySheet->time_sheets : [];
    }

    /**
     * Get filtered time sheets by clinic, date, related day sheets
     * 
     * @param $date
     * @param int $clinic
     */ 
    public function getTimeSheetsWithWorkspace($date, $clinic, $workspace)
    {
        $daySheet = $this->getDaySheetsQuery([$date], $clinic)
                    ->whereHas('time_sheets', function($query) use($workspace) {
                        $query->whereHas('specializations', function($query) use($workspace) {
                            $query->where('workspace_id', '=', $workspace);
                        });
                    })->first();
                    
        return  $daySheet ? $daySheet->time_sheets : collect([]);                    
    }
}