<?php

namespace App\V1\Observers;

use App\V1\Models\Payment;
use App\V1\Models\Appointment\Service;
use App\V1\Contracts\Services\Messenger\Message;

class PaymentObserver
{

    /**
     * Listen to creating event
     * 
     * @param Payment $model
     */ 
    public function creating(Payment $model)
    {
        if (empty($model->created_at)) {
            $model->created_at = now();
        }

        if (empty($model->money_reciever_id)) {
            $model->setMoneyReciever();
        }
    }

    /**
     * Listen to created event
     * 
     * @param Payment $model
     */ 
    public function created(Payment $model)
    {
        if ($model->is_deposit == true && $model->is_prepayment == false) {
            if ($model->type === Payment::TYPE_INCOME) {
                $model->increasePatientDeposit();
            } elseif ($model->type === Payment::TYPE_EXPENSE) {
                $model->decreasePatientDeposit();
            }
        }
        $model->updateCashbox();

        if ($model->service !== null && $model->service->container_type === Service::CONTAINER_ANALYSES) {
            $this->sendResultsToEmail($model);
        }
    }

    /**
     * Listen to updating event
     * 
     * @param Payment $model
     */ 
    public function updating(Payment $model)
    {
        if ($model->type === Payment::TYPE_INCOME) {
            if ($model->is_deleted == true && $model->is_deposit == true) {
                $model->verifyPatientDepositAvailable();
            }

            if ($model->is_deleted != true) {
                $model->verifyCashboxChange();
            }
        }
    }

    /**
     * Listen to updated event
     * 
     * @param Payment $model
     */ 
    public function updated(Payment $model)
    {
        if ($model->is_deleted == true) {
            $cashbox = $model->cashbox;

            if ($model->type === Payment::TYPE_INCOME) {
                if ($model->is_deposit == true) {
                    $model->decreasePatientDeposit();
                }

                if ($model->from_deposit == false) {
                    $model->decreaseCashboxIncome($cashbox);
                }
            }

            if ($model->from_deposit == true) {
                $model->deleteDepositReturn();
                $model->increasePatientDeposit();
            }
            $cashbox->save();
        }
    }

    /**
     * Listen to saved event
     * 
     * @param Payment $model
     */ 
    public function saved(Payment $model)
    {
        $model->updateCacheValidity();
    }

    /**
     * Send result to patient email if possible
     * 
     * @param Payment $model
     */ 
    protected function sendResultsToEmail($model)
    {
        if ($model->service->payed >= $model->service->cost) {
            $patient = $model->patient;
            if ($patient->mailing_analysis == 1) {
                foreach ($model->service->analysis_items as $item) {
                    $result = $item->service;
                    if ($result !== null && 
                        $result->delivery_status === Message::STATUS_NO_DELIVERY && 
                        $result->attachments->count() !== 0) {
                        $result->sendToPatient();
                    }
                }
            }
        }
    }
}