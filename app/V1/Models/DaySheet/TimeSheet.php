<?php

namespace App\V1\Models\DaySheet;

use App\V1\Models\BaseModel;
use App\V1\Models\DaySheet;
use App\V1\Models\Specialization;
use App\V1\Models\Workspace;

class TimeSheet extends BaseModel
{
    public $timestamps = false;
    
    /**
    * @var array
    */
    protected $fillable = [
        'time_from',
        'time_to',
        'day_sheet_id',
        'specializations',
    ];

    /**
     * Related date sheet
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function day_sheet()
    {
        return $this->belongsTo(DaySheet::class);
    }

    /**
     * Related specializations
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function specializations()
    {
        return $this->belongsToMany(Specialization::class, 'specialization_time_sheet')
                    ->withPivot('workspace_id');
    }

    /**
     * Related workspaces
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function workspaces()
    {
        return $this->belongsToMany(Workspace::class, 'specialization_time_sheet');
    }

    /**
     * Related specializations with workspaces
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function specialization_with_workspace()
    {
        return $this->specializations()
                    ->wherePivot('workspace_id', '!=', null);
    }

    /**
     * Get related workspace by id
     */
    public function getWorkspaceById($workspaceId)
    {
        return $this->workspaces()->where('id', '=', $workspaceId)->first();
    }
}
