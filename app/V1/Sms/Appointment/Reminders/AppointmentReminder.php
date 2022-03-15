<?php


namespace App\V1\Sms\Appointment\Reminders;

use App\V1\Models\Appointment;
use App\V1\Models\Employee;
use App\V1\Models\Notification\Template;
use App\V1\Models\SmsAppointmentReminder;
use App\V1\Services\Messenger\Message;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Arr;

class AppointmentReminder extends Message
{
    /**
     * @var Appointment
     */
    protected $appointment;
    protected $appointmentReminder;

    /**
     * Constructor
     *
     * @param Appointment $appointment
     * @param $appointmentReminder
     */
    public function __construct(Appointment $appointment, SmsAppointmentReminder $appointmentReminder)
    {
        $this->appointment = $appointment;
        $this->appointmentReminder = $appointmentReminder;
    }

    /**
     * Build the message.
     *
     * @return AppointmentReminder
     */
    public function build()
    {
        $this->compose([
            'patientFullName' => $this->appointment->patient->full_name,
            'patientFirstName' => $this->appointment->patient->firstname,
            'patientMiddleName' => $this->appointment->patient->middlename,
            'patientLastName' => $this->appointment->patient->lastname,
            'DoctorName' => $this->appointment->doctor->full_name,
            'DoctorInitials' => $this->getInitials($this->appointment->doctor),
            'SpecDoctorRod' => $this->appointment->specialization->name,
            'Date' => Carbon::parse($this->appointment->date)->format('d/m'),
            'Time' => Carbon::parse($this->appointment->date . ' ' . $this->appointment->start)->format('H:i'),
            'Clinic' => $this->appointment->clinic->name,
            'ClinicAddress' => $this->appointment->clinic->address->address,
            'RefClinic' => $this->appointment->clinic->map_link,
            'ClinicPhone' => $this->appointment->clinic->contact_phone,
            'isMale' => $this->appointment->patient->gender === 'male',
            'isDoctor' => $this->appointment->doctor_type === Employee::RELATION_TYPE,
        ]);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getScenario()
    {
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
        return $this->appointment->patient;
    }

    /**
     * @inheritdoc
     */
    public function afterDelivery($success, $number, $result = null)
    {
        if ($success) {
            $this->appointmentReminder->status = Message::STATUS_DELIVERING;
        } else {
            $this->appointmentReminder->status = Message::STATUS_DELIVERY_FAILED;
        }

        if (is_array($result)) {
            $this->appointmentReminder->vendor_id = Arr::get($result, 'results.requestId');
            $this->appointmentReminder->vendor_data = Arr::get($result, 'results');
        }

        $this->appointmentReminder->save();
    }

    /**
     * Convert date to string
     *
     * @param Carbon|string $date
     * @param bool $withTime
     *
     * @return string
     */
    protected function convertDate($date, $withTime = true)
    {
        if ($date === null) {
            return null;
        }

        if (!$date instanceof Carbon) {
            $date = Carbon::parse($date);
        }

        return $date->format($withTime ? 'Y-m-d H:i:s' : 'Y-m-d');
    }

    /**
     * Get shortened doctor name
     * 
     * @param mixed $doctor
     * 
     * @return string
     */
    protected function getInitials($doctor)
    {
        if ($doctor instanceof Employee) {
            $result = $doctor->last_name;
            if ($doctor->first_name) {
                $result .= sprintf(' %s.', mb_substr($doctor->first_name, 0, 1));
            }
            if ($doctor->middle_name) {
                $result .= sprintf(' %s.', mb_substr($doctor->middle_name, 0, 1));
            }
            return $result;
        }

        return $doctor->full_name;
    }
}
