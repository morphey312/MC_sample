<?php

namespace App\V1\Observers;

use App\V1\Contracts\Services\Messenger\Message;
use App\V1\Models\Appointment;
use App\V1\Models\Notification\Template;
use App\V1\Models\SmsAppointmentReminder;
use App\V1\Repositories\AppointmentRepository;
use App\V1\Repositories\Notification\TemplateRepository;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class AppointmentSmsReminderObserver
{

    protected $templatesRepository;
    protected $appointmentsRepository;

    public function __construct()
    {
        $this->templatesRepository = new TemplateRepository();
        $this->appointmentsRepository = new AppointmentRepository();
    }

    /**
     * Listen to created event
     *
     * @param Appointment $appointment
     */
    public function created(Appointment $appointment)
    {
        if (config('services.sms.sms_appointment_reminders')) {
            $now = Carbon::now();
            $OriginalAppointmentDate = Carbon::parse($appointment->date);

            if ($appointment->patient->sms && $appointment->is_first &&
                ($OriginalAppointmentDate->isToday() || $OriginalAppointmentDate->isFuture())) {

                $firstAppointmentByDay = $this->appointmentsRepository
                    ->getPatientClinicFirstAppointmentByDate($appointment->patient_id,
                        $appointment->clinic_id, $appointment->date);

                $smsReminders = $firstAppointmentByDay->sms_reminders()
                    ->whereRaw('DATE(scheduled_at) = ?', [$appointment->date])->get();

                if ($smsReminders->isEmpty()) {

                    $firstAppointmentByDay->clearNotSentAppointmentReminders();

                    $appointmentDate = Carbon::parse($firstAppointmentByDay->date . ' ' . $firstAppointmentByDay->start);

                    $InstantTemplate = $this->getTemplate(
                        Template::SCENARIO_APPOINTMENT_SMS_REMINDER_INSTANT,
                        $firstAppointmentByDay->clinic_id
                    )->first();

                    if (!empty($InstantTemplate)) {
                        $firstAppointmentByDay->queueSmsReminder($InstantTemplate, $now);
                    }

                    if ($appointmentDate->diffInDays($now) >= 5) {
                        $threeDaysTemplate = $this->getTemplate(
                            Template::SCENARIO_SMS_APPOINTMENT_REMINDER_72H,
                            $firstAppointmentByDay->clinic_id
                        )->first();

                        if (!empty($threeDaysTemplate)) {
                            $time = !empty($threeDaysTemplate->time) ? explode(':', $threeDaysTemplate->time) : [9, 0];

                            $firstAppointmentByDay->queueSmsReminder(
                                $threeDaysTemplate,
                                $appointmentDate->copy()->subDays(3)->setTime($time[0], $time[1])
                            );
                        }
                    }

                    if (!$appointmentDate->isToday() && $appointmentDate->isFuture()) {
                        $timeToSubtract = !empty($InstantTemplate->time) ? explode(':', $InstantTemplate->time) : [1, 0];
                        $subtractedTime = $appointmentDate->copy()->subHours($timeToSubtract[0])->subMinutes($timeToSubtract[1]);

                        if (!empty($InstantTemplate)) {
                            $firstAppointmentByDay->queueSmsReminder(
                                $InstantTemplate,
                                $appointmentDate->copy()
                                    ->setTime($subtractedTime->format('H'), $subtractedTime->format('i')));

                        }
                    }
                }
            }
        }
    }

    /**
     * Listen to updated event
     *
     * @param Appointment $appointment
     */
    public function updated(Appointment $appointment)
    {
        if (config('services.sms.sms_appointment_reminders')) {
            if (!$appointment->is_first || $appointment->is_deleted) {
                $firstAppointmentByDay = $this->appointmentsRepository
                    ->getPatientClinicFirstAppointmentByDate($appointment->patient_id,
                        $appointment->clinic_id, $appointment->date);

                if($firstAppointmentByDay){
                    $firstAppointmentByDay->clearNotSentAppointmentReminders();
                }

                $appointment->clearNotSentAppointmentReminders();
            }
        }
    }

    public function getTemplate($scenario, $clinicID)
    {
        return $this->templatesRepository->getUsableTemplates(
            $scenario,
            $clinicID
        );
    }
}
