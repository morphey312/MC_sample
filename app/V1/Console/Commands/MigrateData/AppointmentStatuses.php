<?php

namespace App\V1\Console\Commands\MigrateData;

class AppointmentStatuses extends BaseMigrate
{
    /**
     * @var string
     */
    protected $srcTable = 'list_status';
    
    /**
     * @var string
     */
    protected $destTable = 'appointment_statuses';
    
    /**
     * @var string
     */
    protected $remoteKey = 'id_status';
    
    /**
     * @var bool
     */  
    protected $shouldPatch = true;
    
    /**
     * @inherit
     */ 
    protected function prepareData($data)
    {
        return $this->pickData($data, [
            'name' => $this->toUTF('description'),
            'comment_required' => $this->toBool('need_note'),
            'status_reason' => $this->toBool('need_reason'),
            'is_active' => $this->toBool('is_active_status'),
            'service_in_cost' => $this->toBool('is_count_payment'),
            'patient_card_required' => $this->toBool('is_need_card'),
            'service_in_order' => $this->toBool('is_count_prihod'),
            'sms_for_card' => $this->toBool('is_for_SMS_by_card'),
            'color' => ['brush_color', function($v) {
                return sprintf('#%06x', $v);
            }],
        ]);
    }
}