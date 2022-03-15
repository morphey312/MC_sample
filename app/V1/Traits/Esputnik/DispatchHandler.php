<?php


namespace App\V1\Traits\Esputnik;


use App\V1\Contracts\Repositories\HandbookRepository;
use App\V1\Contracts\Services\Esputnik\Event;
use App\V1\Facades\Esputnik;
use App\V1\Models\Appointment;
use Illuminate\Support\Carbon;

trait DispatchHandler
{
    public function process()
    {
        $city = null;
        if (
            $this->appointment->relationLoaded('patient')
            && $this->appointment->patient->email
            && $this->appointment->patient->primary_phone
        ) {
            if ($this->appointment->relationLoaded('clinic')) {
                $city = app(HandbookRepository::class)->findCity($this->appointment->clinic->city, false);
            }



            /**
             * @var Event $esputnikEvent
             */
            $provider = $this->template->provider()->get()->first();
            $esputnikEvent = Esputnik::newEvent($provider);
            $patient = $this->appointment->patient;
            $json = [
                'firstName' => $patient->firstname,
                'visitDate' => sprintf('%sT%s',
                    Carbon::parse($this->appointment->date)->format('Y-m-d'),
                    Carbon::parse($this->appointment->start)->format('H:i')
                ),
                'visitDays' => Carbon::parse($this->appointment->date)->diffInDays(now()),
                'visitTypes' => $this->getVisitTypes($this->appointment),
                'visitSpec' => $this->appointment->specialization->name
            ];

            if ($this->appointment->relationLoaded('doctor')) {
                $json['visitDoc'] = $this->appointment->doctor->full_name;
            }

            if ($this->appointment->relationLoaded('clinic')) {
                $json['cAddress'] = $this->appointment->clinic->address;
            }

            if ($city) {
                $json['cName'] = $city->value;
            }
            return $esputnikEvent
                ->setType('VisitPlan')
                ->setTarget($patient->email->value)
                ->addParam('email', $patient->email->value)
                ->addParam('json', json_encode($json));
        }

    }

    protected function getVisitTypes(Appointment $appointment)
    {
        $list = [];

        $appointment->appointment_services->map(function ($service) use (&$list) {
            $list[] = [
                'visitType' => $service->service->name,
                'visitSpec' => $service->service->specialization->name
            ];
        });

        $appointment->analysis_results->map(function ($service) use (&$list) {
            $list[] = [
                'visitType' => $service->analysis->name,
            ];
        });

        return $list;
    }
}
