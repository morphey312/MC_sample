<?php

namespace App\V1\Models\Analysis;

use App\V1\Models\BaseModel;
use App\V1\Models\Analysis;
use App\V1\Repositories\AnalysisRepository;

class Candidate extends BaseModel
{
    /**
     * Model related table
     *
     * @var string
     */
    protected $table = 'analysis_candidates';

    /**
     * @var array
     */
    protected $fillable = [
        'name',
        'name_lc1',
        'name_lc2',
        'name_lc3',
        'code',
        'disabled',
        'notes',
        'lab_analysis_id',
        'laboratories',
    ];

    /**
     * @var array
     */
    protected $casts = [
        'disabled' => 'boolean',
        'laboratories' => 'array',
    ];

    /**
     * Related analysis
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function analysis()
    {
        return $this->hasOne(Analysis::class);
    }

    public function bindAnalysis()
    {
        $repository = app(AnalysisRepository::class);
        $analysis = $repository->first($repository->filter([
            'name' => $this->name,
            'laboratory_code' => $this->code,
            'candidate_id' => null,
        ]));

        if ($analysis) {
            $analysis->candidate_id = $this->id;
            $analysis->save();
        }
    }
}
