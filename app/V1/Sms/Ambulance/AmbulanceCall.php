<?php


namespace App\V1\Sms\Ambulance;

use App\V1\Models\AmbulanceCall as AmbulanceCallModel;
use App\V1\Models\Appointment;
use App\V1\Models\Employee;
use App\V1\Models\Notification\Template;
use App\V1\Models\SmsAppointmentReminder;
use App\V1\Services\Messenger\Message;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Arr;

class AmbulanceCall extends Message
{
    /**
     * @var Appointment
     */
    protected $appointment;
    protected $ambulanceCall;

    /**
     * Constructor
     *
     * @param Appointment $appointment
     * @param $ambulanceCall
     */
    public function __construct(Appointment $appointment, AmbulanceCallModel $ambulanceCall)
    {
        $this->appointment = $appointment;
        $this->ambulanceCall = $ambulanceCall;
    }

    /**
     * Build the message.
     *
     * @return AmbulanceCall
     */
    public function build()
    {
        $this->compose([
            'patientFullName' => $this->appointment->patient->full_name,
            'patientFirstName' => $this->appointment->patient->firstname,
            'patientMiddleName' => $this->appointment->patient->middlename,
            'patientLastName' => $this->appointment->patient->lastname,
            'patientAge' => $this->appointment->patient->age,
            'DoctorName' => $this->appointment->doctor->full_name,
            'SpecDoctorRod' => $this->appointment->specialization->name,
            'Date' => Carbon::parse($this->appointment->date)->format('d/m'),
            'Time' => Carbon::parse($this->appointment->date . ' ' . $this->appointment->start)->format('H:i'),
            'Clinic' => $this->appointment->clinic->name,
            'ClinicAddress' => $this->appointment->clinic->address->address,
            'RefClinic' => $this->appointment->clinic->map_link,
            'ClinicPhone' => $this->appointment->clinic->contact_phone,
            'isMale' => $this->appointment->patient->gender === 'male',
            'isDoctor' => $this->appointment->doctor_type === Employee::RELATION_TYPE,
            'Amb_call_phone' => $this->ambulanceCall->phone,
            'Amb_caller' => $this->ambulanceCall->caller,
            'Amb_phone' => $this->ambulanceCall->phone,
            'Amb_street' => $this->ambulanceCall->street,
            'Amb_district' => $this->ambulanceCall->district,
            'Amb_house' => $this->ambulanceCall->house,
            'Amb_reason' => $this->ambulanceCall->reason,
            'Amb_porch' => $this->ambulanceCall->porch,
            'Amb_flat' => $this->ambulanceCall->flat,
            'Amb_storey' => $this->ambulanceCall->storey,
            'Amb_comment' => $this->ambulanceCall->comment,
            'Amb_time' => Carbon::parse($this->ambulanceCall->created_at)->format('H:i'),
        ]);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getScenario()
    {
        return Template::SCENARIO_SMS_AMBULANCE_CALL;
    }

    /**
     * @inheritdoc
     */
    public function getClinicId()
    {
        return $this->appointment->clinic_id;
    }

    /**
     * @inheritdoc
     */
    public function getRecipient()
    {
        return $this->appointment->doctor;
    }
}
