<?php

namespace App\V1\Models\Patient\Card;

use App\V1\Models\BaseModel;
use App\V1\Models\Analysis\Result;
use App\V1\Models\Patient\AssignedMedicine;
use App\V1\Models\Patient\AssignedService;
use App\V1\Models\Patient\OutclinicService;
use App\V1\Repositories\Analysis\ResultRepository;
use App\V1\Repositories\Patient\AssignedServiceRepository;

class Assignment extends BaseModel
{
    const RELATION_TYPE = 'card_assignment';

    const ANALYSIS_RESULTS = 'analysis_results';
    const ASSIGNED_MEDICINES = 'assigned_medicines';
    const ASSIGNED_PROCEDURES = 'assigned_procedures';
    const ASSIGNED_PHYSIOTHERAPIES = 'assigned_physiotherapies';
    const ASSIGNED_DIAGNOSTICS = 'assigned_diagnostics';
    const OUTCLINIC_SERVICES = 'outclinic_services';
    const SURGERY_BASE_SERVICES = 'surgery_base_services';
    const SURGERY_SERVICES = 'surgery_services';

    /**
     * Model table
     *
     * @var string
     */
    protected $table = 'card_assignments';

    /**
     * @var array
     */
    protected $fillable = [
        'type',
        'analysis_results',
        'assigned_medicines',
        'assigned_procedures',
        'assigned_physiotherapies',
        'assigned_diagnostics',
        'outclinic_services',
        'surgery_base_services',
        'surgery_services',
    ];

    /**
     * Related analysis_results
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function analysis_results()
    {
        return $this->hasMany(Result::class, 'card_assignment_id');
    }

    /**
     * Related analysis_results
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function assigned_medicines()
    {
        return $this->hasMany(AssignedMedicine::class, 'card_assignment_id');
    }

    /**
     * Related assigned_procedures
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function assigned_procedures()
    {
        return $this->hasMany(AssignedService::class, 'card_assignment_id');
    }

    /**
     * Related assigned_physiotherapies
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function assigned_physiotherapies()
    {
        return $this->hasMany(AssignedService::class, 'card_assignment_id');
    }

    /**
     * Related assigned_diagnostics
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function assigned_diagnostics()
    {
        return $this->hasMany(AssignedService::class, 'card_assignment_id');
    }

    /**
     * Related outclinic_services
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function outclinic_services()
    {
        return $this->hasMany(OutclinicService::class, 'card_assignment_id');
    }

    /**
     * Related surgery_base_services
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function surgery_base_services()
    {
        return $this->hasMany(AssignedService::class, 'card_assignment_id');
    }

    /**
     * Related surgery_services
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function surgery_services()
    {
        return $this->hasMany(AssignedService::class, 'card_assignment_id');
    }

    /**
     * Related card_record
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function card_record()
    {
        return $this->morphOne(Record::class, 'recordable');
    }

    /**
     * Get assignment description
     *
     * @return array
     */
    public function getDescriptionAttribute()
    {
        switch ($this->type) {
            case self::ANALYSIS_RESULTS:
                return $this->analysis_results->map(function($item) {
                    return $item->analysis->name;
                });

            case self::ASSIGNED_MEDICINES:
                return $this->assigned_medicines->map(function($item) {
                    return $item->medicine->name;
                });

            case self::ASSIGNED_PROCEDURES:
                return $this->assigned_procedures->map(function($item) {
                    return $item->service->name;
                });

            case self::ASSIGNED_PHYSIOTHERAPIES:
                return $this->assigned_physiotherapies->map(function($item) {
                    return $item->service->name;
                });

            case self::ASSIGNED_DIAGNOSTICS:
                return $this->assigned_diagnostics->map(function($item) {
                    return $item->service->name;
                });

            case self::OUTCLINIC_SERVICES:
                return $this->outclinic_services->map(function($item) {
                    return $item->name;
                });

            case self::SURGERY_BASE_SERVICES:
                return $this->surgery_base_services->map(function($item) {
                    return $item->service->name;
                });

            case self::SURGERY_SERVICES:
                return $this->surgery_services->map(function($item) {
                    return $item->service->name;
                });
        }

        return [];
    }

    /**
     * Archive same assignments
     *
     * @return array
     */
    public function archiveSameAssignments(Record $model)
    {
        $assignment_ids = [];
        $service_ids = [];
        $patient_id = $model->card_specialization->card->patient_id;
        $recorable = $model->recordable;

        switch ($recorable->type) {
            case self::ANALYSIS_RESULTS:
                $assignment_ids = $recorable->analysis_results->pluck('id');
                $service_ids = $recorable->analysis_results->pluck('analysis_id');
                break;

            case self::ASSIGNED_PROCEDURES:
                $assignment_ids = $recorable->assigned_procedures->pluck('id');
                $service_ids = $recorable->assigned_procedures->pluck('service_id');
                break;

            case self::ASSIGNED_PHYSIOTHERAPIES:
                $assignment_ids = $recorable->assigned_physiotherapies->pluck('id');
                $service_ids = $recorable->assigned_physiotherapies->pluck('service_id');
                break;

            case self::ASSIGNED_DIAGNOSTICS:
                $assignment_ids = $recorable->assigned_diagnostics->pluck('id');
                $service_ids = $recorable->assigned_diagnostics->pluck('service_id');
                break;

            case self::SURGERY_BASE_SERVICES:
                $assignment_ids = $recorable->surgery_base_services->pluck('id');
                $service_ids = $recorable->surgery_base_services->pluck('service_id');
                break;

            case self::SURGERY_SERVICES:
                $assignment_ids = $recorable->surgery_services->pluck('id');
                $service_ids = $recorable->surgery_services->pluck('service_id');
                break;
        }

        if (count($service_ids) > 0 && count($assignment_ids) > 0) {
            if ($recorable->type === self::ANALYSIS_RESULTS) {
                $repository = app(ResultRepository::class);
                $repository->updateSameAnalysisForPatient($assignment_ids, $service_ids, $model->card_specialization_id, $patient_id);
            } else {
                $repository = app(AssignedServiceRepository::class);
                $repository->updateSameServicesForPatient($assignment_ids, $service_ids, $model->card_specialization_id, $patient_id);
            }
        }
    }
}
