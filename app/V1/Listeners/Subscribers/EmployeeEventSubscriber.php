<?php

namespace App\V1\Listeners\Subscribers;

use App\V1\Models\Employee;
use App\V1\Models\Handbook;
use App\V1\Models\Patient\InformationSource;
use App\V1\Repositories\HandbookRepository;

class EmployeeEventSubscriber
{
    /**
     * @var HandbookRepository
     */
    protected $repository;

    /**
     * EmployeeEventSubscriber constructor.
     *
     * @param HandbookRepository $repository
     */
    public function __construct(HandbookRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Add clinics to employee information source after create
     *
     * @param Employee $employee
     *
     * @return void
     */
    public function handleAfterCreateEmployee(Employee $employee)
    {
        $clinicIds = $employee->clinics->pluck('id');
        $mediaType = $this->repository->getByServiceKey(Handbook::CATEGORY_MEDIA_TYPE, 'recommendation');

        if ($mediaType !== null) {
            $informationSourceData['name'] = 'Рекомендация_'.$employee->getEmployeeInitialsAttribute();
            $informationSourceData['is_active'] = 1;
            $informationSourceData['is_collective_form'] = false;
            $informationSourceData['show_in_appointment'] = true;
            $informationSourceData['media_type'] = $mediaType->id;
            $informationSourceData['clinics'] = $clinicIds;
            $informationSourceData['employee_id'] = $employee->id;

            $informationSourceModel = new InformationSource($informationSourceData);
            $informationSourceModel->save();
        }
    }

    /**
     * Add clinics to employee information source after update
     *
     * @param Employee $employee
     *
     * @return void
     */
    public function handleAfterUpdateEmployee(Employee $employee)
    {
        $EmployeeClinicIds = $employee->clinics->pluck('id')->toArray();

        $informationSource = $employee->information_source;
        $informationSourceClinicIds = $informationSource->clinics->pluck('id')->toArray();
        $informationSource->name = 'Рекомендация_'.$employee->getEmployeeInitialsAttribute();

        if (array_diff($EmployeeClinicIds, $informationSourceClinicIds)) {
            $informationSource->clinics = $EmployeeClinicIds;
            $informationSource->save();
        }

        $informationSource->save();
    }

    /**
     * Listening events
     *
     * @param $events
     *
     * @return void
     */
    public function subscribe($events)
    {
        $events->listen(
            'AfterCreateEmployeeEvent',
            'App\V1\Listeners\Subscribers\EmployeeEventSubscriber@handleAfterCreateEmployee'
        );
        $events->listen(
            'AfterUpdateEmployeeEvent',
            'App\V1\Listeners\Subscribers\EmployeeEventSubscriber@handleAfterUpdateEmployee'
        );
    }
}
