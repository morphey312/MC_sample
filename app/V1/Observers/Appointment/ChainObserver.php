<?php

namespace App\V1\Observers\Appointment;

use App\V1\Models\Appointment;
use App\V1\Models\Call;
use App\V1\Models\Patient;
use App\V1\Models\Appointment\Status;
use DB;
use App\V1\Jobs\Elastic\Report\CallCenter\CallSlicesJob;
use App\V1\Jobs\Elastic\Report\CallCenter\CallBonusJob;

class ChainObserver
{
    /**
     * Listen to created event
     * 
     * @param Appointment $model
     */ 
    public function created(Appointment $model)
    {
        $this->linkToPrevious($model);
        
        if ($model->is_first) {
            $this->updateCallStatus($model);
            $this->updateAppointmentStatus($model);
        }
    }
    
    /**
     * Listen to updated event
     * 
     * @param Appointment $model
     */ 
    public function updated(Appointment $model)
    {
        if ($this->wasDeleted($model)) {
            $next = $model->next_appointment;
            if ($next !== null) {
                $this->linkToPrevious($next);
            }
        } elseif ($this->wasChangedSpecialization($model) || 
            $this->wasChangedPatient($model)) 
        {
            $next = $model->next_appointment;
            if ($next !== null) {
                $this->linkToPrevious($next);
            }
            $this->linkToPrevious($model);
        } elseif ($this->wasMoved($model)) {
            $this->linkToPrevious($model);
        }
        
        if ($model->is_first && !$model->is_deleted) {
            if ($this->wasChangedIsFirst($model) || 
                $this->wasChangedPatient($model) ||
                $this->wasChangedSpecialization($model) ||
                $this->wasChangedToIncome($model)) 
            {
                $this->updateCallStatus($model);
                $this->updateAppointmentStatus($model);
            }
        }
    }
    
    /**
     * Check if appointment was moved to another time
     * 
     * @param Appointment $model
     * 
     * @return bool
     */ 
    protected function wasMoved($model)
    {
        return $model->isDirty(['date', 'start']);
    }
    
    /**
     * Check if appointment was deleted
     * 
     * @param Appointment $model
     * 
     * @return bool
     */ 
    protected function wasDeleted($model)
    {
        return $model->is_deleted 
            && $model->isDirty('is_deleted');
    }
    
    /**
     * Check if appointment is_first was changed
     * 
     * @param Appointment $model
     * 
     * @return bool
     */ 
    protected function wasChangedIsFirst($model)
    {
        return $model->isDirty('is_first');
    }
    
    /**
     * Check if appointment specialization was changed
     * 
     * @param Appointment $model
     * 
     * @return bool
     */ 
    protected function wasChangedSpecialization($model)
    {
        return $model->isDirty('specialization_id');
    }
    
    /**
     * Check if appointment patient was changed
     * 
     * @param Appointment $model
     * 
     * @return bool
     */ 
    protected function wasChangedPatient($model)
    {
        return $model->isDirty('patient_id');
    }
    
    /**
     * Check if appointment was changed to income
     * 
     * @param Appointment $model
     * 
     * @return bool
     */ 
    protected function wasChangedToIncome($model)
    {
        if ($model->isDirty('appointment_status_id')) {
            if ($model->status->service_in_cost == 1) {
                $prevStatus = Status::find($model->getOriginal('appointment_status_id'));
                return $prevStatus === null || $prevStatus->service_in_cost != 1;
            }
        }
        
        return false;
    }
    
    /**
     * Link this appointment to the previous in chain
     * 
     * @param Appointment $model
     */ 
    protected function linkToPrevious($model)
    {
        $prev = $this->findPreviousAppointment($model);
        
        if ($prev !== null) {
            if ($model->prev_appointment_id != $prev->id) {
                $nextPrev = $prev->next_appointment;
                
                $model->where('id', $model->id)->update([
                    'prev_appointment_id' => $prev->id,
                ]);
                
                if ($nextPrev !== null) {
                    $this->linkToPrevious($nextPrev);
                }
            }
        } elseif ($model->prev_appointment_id != null) {
            $model->where('id', $model->id)->update([
                'prev_appointment_id' => null,
            ]);
        }
    }
    
    /**
     * Find appointment that was before the given
     * 
     * @param Appointment $model
     * 
     * @return Appointment
     */ 
    protected function findPreviousAppointment($model)
    {
        return Appointment::where('patient_id', '=', $model->patient_id)
            ->where('specialization_id', '=', $model->specialization_id)
            ->where('clinic_id', '=', $model->clinic_id)
            ->where('is_deleted', '=', 0)
            ->where(function($query) use($model) {
                $query->where('date', '<', $model->date)
                    ->orWhere(function($query) use($model) {
                        $query->where('date', '=', $model->date)
                            ->where('start', '<', $model->start);
                    });
            })
            ->orderBy('date', 'desc')
            ->orderBy('start', 'desc')
            ->first();
    }
    
    /**
     * Set status to secondary on the leading call
     * 
     * @param Appointment $model
     */ 
    protected function updateCallStatus($model)
    {
        $calls = Call::where('contact_id', '=', $model->patient_id)
            ->where('contact_type', '=', Patient::RELATION_TYPE)
            ->where('specialization_id', '=', $model->specialization_id)
            ->where('clinic_id', '=', $model->clinic_id)
            ->where('is_first', '=', 1)
            ->pluck('id')
            ->all();
        
        Call::whereIn('id', $calls)
            ->update([
                 'is_first' => 0,
            ]);

        if (config('services.elasticsearch.enable_cache')) {
            foreach ($calls as $id) {
                CallSlicesJob::dispatch($id)->onQueue('elastic');
                CallBonusJob::dispatch($id)->onQueue('elastic');
            }
        }
    }
    
    /**
     * Set status to secondary on appointments where patient did not come
     * 
     * @param Appointment $model
     */ 
    protected function updateAppointmentStatus($model)
    {
        Appointment::where('id', '!=', $model->id)
            ->where('patient_id', '=', $model->patient_id)
            ->where('specialization_id', '=', $model->specialization_id)
            ->where('clinic_id', '=', $model->clinic_id)
            ->where('is_first', '=', 1)
            ->where(function($query) {
                $query->where('is_deleted', '=', 1)
                    ->orWhereIn('appointment_status_id', function($query) {
                        $query->select('appointment_statuses.id')
                            ->from('appointment_statuses')
                            ->whereIn('appointment_statuses.system_status', [
                                Appointment::STATUS_DELETED,
                                Appointment::STATUS_DIDNT_COME,
                            ]);
                    });
            })->update([
                'is_first' => 0,
            ]);
    }
}