<?php

namespace App\V1\Services\Merge;

use App\V1\Contracts\Services\Merge\Service as MergeInterface;
use App\V1\Contracts\Repositories\ServiceRepository;
use App\V1\Traits\Services\Merge\TransferPrice;

class Service implements MergeInterface
{
    use TransferPrice;
    
    /**
     * @var ServiceRepository
     */
    protected $repository;
    
    /**
     * Service constructor
     *
     * @param  ServiceRepository $repository
     */
    public function __construct(ServiceRepository $repository)
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
        
        foreach ($source as $service) {
            $this->mergeInto($service, $destination);
        }

        if ($destination->isDirty()) {
            $destination->save();
        }
        
        return true;
    }
    
    /**
     * Merge two services
     * 
     * @param \App\V1\Models\Service $source
     * @param \App\V1\Models\Service $destination
     */ 
    protected function mergeInto($source, $destination)
    {
        foreach ($source->prices as $price) {
            $this->transferPrice($price, $destination);
        }
        
        $destination->reparent($source, [
            'appointment_services',
            'assigned_services',
            'site_enquiry_services',
        ]);
        
        $this->mergeClinics($source, $destination);
        $this->mergeProtocols($source, $destination);
        $this->mergeDetails($source, $destination);
        
        $source->delete();
    }
    
    /**
     * Merge clinics of two services
     * 
     * @param \App\V1\Models\Service $source
     * @param \App\V1\Models\Service $destination
     */
    protected function mergeClinics($source, $destination)
    {
        $destClinics = $destination->clinics;
        foreach ($source->clinics as $clinic) {
            if ($destClinics->where('id', $clinic->id)->first() === null) {
                $destClinics->add($clinic);
                $destination->clinics()->attach($clinic, [
                    'code' => $clinic->pivot->code,
                ]);
            }
        }
    }
    
    /**
     * Merge protocols of two services
     * 
     * @param \App\V1\Models\Service $source
     * @param \App\V1\Models\Service $destination
     */
    protected function mergeProtocols($source, $destination)
    {
        $destTemplates = $destination->protocol_templates;
        foreach ($source->protocol_templates as $template) {
            if ($destTemplates->where('id', $template->id)->first() === null) {
                $destTemplates->add($template);
                $destination->protocol_templates()->attach($template);
            }
        }
    }

    /**
     * Merge info from two services
     * 
     * @param \App\V1\Models\Service $source
     * @param \App\V1\Models\Service $destination
     */
    protected function mergeDetails($source, $destination)
    {
        $attribs = [
            'name_lc1',
            'name_lc2',
            'name_lc3',
            'name_ua',
            'name_ua_lc1',
            'name_ua_lc2',
            'name_ua_lc3',
        ];

        foreach ($attribs as $attribute) {
            $destValue = $destination->getAttributeValue($attribute);
            if (empty($destValue)) {
                $srcValue = $source->getAttributeValue($attribute);
                if (!empty($srcValue)) {
                    $destination->setAttribute($attribute, $srcValue);
                }
            }
        }
    }
}