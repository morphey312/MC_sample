<?php

namespace App\V1\Services\Merge;

use App\V1\Contracts\Services\Merge\Analysis as MergeInterface;
use App\V1\Contracts\Repositories\AnalysisRepository;
use App\V1\Traits\Services\Merge\TransferPrice;

class Analysis implements MergeInterface
{
    use TransferPrice;
    
    /**
     * @var AnalysisRepository
     */
    protected $repository;
    
    /**
     * Analysis constructor
     *
     * @param  AnalysisRepository $repository
     */
    public function __construct(AnalysisRepository $repository)
    {
        $this->repository = $repository;
    }
    
    /**
     * @inherit
     */ 
    public function merge($what, $where) 
    {
        $destination = $this->repository->find($where, false);
        if ($destination === null) {
            return false;
        }
        
        $source = $this->repository->getById($what);
        if ($source->count() === 0) {
            return false;
        }
        
        foreach ($source as $analysis) {
            $this->mergeInto($analysis, $destination);
        }
        
        return true;
    }
    
    /**
     * Merge two analyses
     * 
     * @param \App\V1\Models\Analysis $source
     * @param \App\V1\Models\Analysis $destination
     */ 
    protected function mergeInto($source, $destination)
    {
        foreach ($source->prices as $price) {
            $this->transferPrice($price, $destination);
        }
        
        $destination->reparent($source, [
            'results',
        ]);
        
        $this->mergeClinics($source, $destination);
        
        $source->delete();
    }
    
    /**
     * Merge clinics of two analyses
     * 
     * @param \App\V1\Models\Analysis $source
     * @param \App\V1\Models\Analysis $destination
     */
    protected function mergeClinics($source, $destination)
    {
        $destClinics = $destination->clinics;
        foreach ($source->clinics as $clinic) {
            if ($destClinics->where('id', $clinic->id)->first() === null) {
                $destClinics->add($clinic);
                $destination->clinics()->attach($clinic, [
                    'code' => $clinic->pivot->code, 
                    'duration_days' => $clinic->pivot->duration_days, 
                ]);
            }
        }
    }
}