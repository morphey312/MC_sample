<?php

namespace App\V1\Console\Commands\MigrateData;

use App\V1\Models\Analysis\Result;

class AppointmentAnalyses extends BaseMigrate
{
    const ANALYSIS_MARK = 'for_analyses';
    const CONTAINER_TYPE = 'analysis_results';
    const ANALYSIS_NAME = 'Аналізи';
    
    /**
     * @var string
     */
    protected $srcTable = 'RecordPatientServiceAnalyse';
    
    /**
     * @var string
     */
    protected $destTable = 'appointment_service_items';
    
    /**
     * @var string
     */
    protected $remoteKey = 'id';
    
    /**
     * @var bool
     */ 
    protected $mapRefs = false;
    
    /**
     * @var int
     */
    protected $analysesContainerId;
    
     /**
     * @inherit
     */ 
    public function __construct($command)
    {
        parent::__construct($command);
        
        $paymentDestId = $this->getLocalConnection()
            ->table('service_payment_destinations')
            ->where('additional_service_mark', self::ANALYSIS_MARK)
            ->value('id');
        
        if ($paymentDestId === null) {
            $paymentDestId = $this->getLocalConnection()
                ->table('service_payment_destinations')
                ->insertGetId([
                    'name' => self::ANALYSIS_NAME,
                    'additional_service_mark' => self::ANALYSIS_MARK,
                    'created_at' => $this->timestamp(),
                    'updated_at' => $this->timestamp(),
                    'company_id' => $this->companyId,
                    'created_by_id' => self::$createdById,
                ]);
        }
        
        $this->analysesContainerId = $this->getLocalConnection()
            ->table('services')
            ->where('payment_destination_id', $paymentDestId)
            ->value('id');
        
        if ($this->analysesContainerId === null) {
            $specializationId = $this->getLocalConnection()
                ->table('specializations')
                ->where('name', self::ANALYSIS_NAME)
                ->value('id');
            
            if ($specializationId === null) {
                $specializationId = $this->getLocalConnection()
                    ->table('specializations')
                    ->insertGetId([
                        'name' => self::ANALYSIS_NAME,
                        'genitive_name' => self::ANALYSIS_NAME,
                        'created_at' => $this->timestamp(),
                        'updated_at' => $this->timestamp(),
                        'company_id' => $this->companyId,
                        'created_by_id' => self::$createdById,
                    ]);
            }
            
            $this->analysesContainerId = $this->getLocalConnection()
                ->table('services')
                ->insertGetId([
                    'name' => self::ANALYSIS_NAME,
                    'name_ua' => self::ANALYSIS_NAME,
                    'specialization_id' => $paymentDestId,
                    'payment_destination_id' => $specializationId,
                    'created_at' => $this->timestamp(),
                    'updated_at' => $this->timestamp(),
                    'company_id' => $this->companyId,
                    'created_by_id' => self::$createdById,
                ]);
        }
    }
    
    /**
     * @inherit
     */ 
    protected function prepareData($data)
    {
        $analysisId = $this->getReference('list_service_analyse', $data->id_analyse);
        if (!$analysisId) {
            return false;
        }
        
        $appointmentId = $this->getReference('RecordPatient', $data->id_record);
        if (!$appointmentId) {
            return false;
        }
        
        $appointment = $this->getLocalConnection()
                ->table('appointments')
                ->where('id', $appointmentId)
                ->first();
        if (!$appointment) {
            return false;
        }
        
        $data->cnt = 1;
        $data->finalcost = floatval($data->price) * $data->cnt * (1 - floatval($data->procent_discount) / 100);
        
        $analysisResultId = $this->getAnalysisResult($appointment, $analysisId, $data);
        if (!$analysisResultId) {
            return false;
        }
        
        if ($this->checkIsDuplicate($appointment->id, $analysisResultId)) {
            return false;
        }
        
        $result = $this->pickData($data, [
            'cost' => $this->toDecimal('finalcost', true),
            'quantity' => $this->toInt('cnt', true),
            'discount' => $this->toDecimal('procent_discount', true),
        ], false, false);
        
        $result['service_id'] = $analysisResultId;
        $result['service_type'] = Result::RELATION_TYPE;
        
        $containerId = $this->getContainerId($appointment, $result['cost']);
        
        if (!$containerId) {
            return false;
        }
        
        $result['appointment_service_id'] = $containerId;
        
        return $result;
    }
    
    /**
     * Check if that record already exists
     */ 
    protected function checkIsDuplicate($appointmentId, $analysisResultId)
    {
        return $this->getLocalConnection()
            ->table('appointment_services')
            ->join('appointment_service_items', function($join) use($analysisResultId) {
                $join->on('appointment_service_items.appointment_service_id', '=', 'appointment_services.id')
                    ->where('appointment_service_items.service_id', '=', $analysisResultId)
                    ->where('appointment_service_items.service_type', '=', Result::RELATION_TYPE);
            })
            ->where('appointment_services.appointment_id', '=', $appointmentId)
            ->exists();
    }
    
    /**
     * Get ID of service container
     * 
     * @param object $$appointment
     * @param float $cost
     * 
     * @return int
     */ 
    protected function getContainerId($appointment, $cost)
    {
        $id = $this->getLocalConnection()
            ->table('appointment_services')
            ->where('service_id', $this->analysesContainerId)
            ->where('appointment_id', $appointment->id)
            ->value('id');
        
        if ($id === null) {
            return $this->getLocalConnection()
                ->table('appointment_services')
                ->insertGetId([
                    'service_id' => $this->analysesContainerId,
                    'appointment_id' => $appointment->id,
                    'patient_id' => $appointment->patient_id,
                    'clinic_id' => $appointment->clinic_id,
                    'container_type' => self::CONTAINER_TYPE,
                    'cost' => $cost,
                ]);
        }
        
        if ($cost > 0) {
            $this->getLocalConnection()
                ->table('appointment_services')
                ->where('id', $id)
                ->increment('cost', $cost);
        }
        
        return $id;
    }
    
    /**
     * Get/create analysis result
     * 
     * @param object $appointment
     * @param int $analysisId
     * @param object $data
     * 
     * @return int
     */ 
    protected function getAnalysisResult($appointment, $analysisId, $data)
    {
        $analysisResultId = $this->getLocalConnection()
            ->table('analysis_results')
            ->where('appointment_id', $appointment->id)
            ->where('analysis_id', $analysisId)
            ->value('id');
        
        if (!$analysisResultId) {
            $analysisResultId = $this->getLocalConnection()
                ->table('analysis_results')
                ->insertGetId([
                    'analysis_id' => $analysisId,
                    'patient_id' => $appointment->patient_id,
                    'assigner_id' => $appointment->doctor_type === 'employees' ? $appointment->doctor_id : null,
                    'card_specialization_id' => $appointment->card_specialization_id,
                    'appointment_id' => $appointment->id,
                    'clinic_id' => $appointment->clinic_id,
                    'quantity' => $data->cnt,
                    'cost' => $data->finalcost,
                    'discount' => 0,
                    'date_expected_pass' => $data->date_analyse_predv,
                    'date_pass' => $data->date_analyse,
                    'date_expected_ready' => $data->date_analyse_ready_predv,
                    'date_ready' => $data->date_analyse_ready,
                    'date_sent_email' => $data->date_sent_email,
                    'status' => !empty($data->date_sent_email) 
                        ? 'email_sent'
                        : (!empty($data->date_analyse_ready) 
                            ? 'ready' 
                            : (!empty($data->date_analyse) ? 'passed' : 'assigned')),
                ]);
        }
        
        return $analysisResultId;
    }
    
    /**
     * @inherit
     */ 
    protected function customizeQuery($query)
    {
        $query->selectRaw(implode(', ', [
                'RecordPatientServiceAnalyse.*',
                'list_patient_card.id_clinic',
                'RecordPatient.id_patient',
                'list_service_analyse_tarif.id_tarif',
                'list_service_analyse_tarif.price',
            ]))
            ->join('RecordPatient', 'RecordPatient.id_record', '=', 'RecordPatientServiceAnalyse.id_record')
            ->join('list_patient_card', 'list_patient_card.id_card_record', '=', 'RecordPatient.id_card_record')
            ->join('list_service_analyse_tarif', function($join) {
                $join->on('list_service_analyse_tarif.id_analyse', '=', 'RecordPatientServiceAnalyse.id_analyse')
                    ->on('list_service_analyse_tarif.date1', '<=', 'RecordPatient.date_')
                    ->on('list_service_analyse_tarif.date2', '>=', 'RecordPatient.date_');
            })
            ->join('list_service_analyse_tarif_clinic', function($join) {
                $join->on('list_service_analyse_tarif_clinic.id_tarif', '=', 'list_service_analyse_tarif.id_tarif')
                    ->on('list_service_analyse_tarif_clinic.id_clinic', '=', 'list_patient_card.id_clinic');
            });
            
        return $query;
    }
}