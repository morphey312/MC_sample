<?php

namespace App\V1\Observers\Clinic;

use App\V1\Models\Clinic;
use App\V1\Models\Handbook;
use App\V1\Contracts\Repositories\Patient\InformationSourceRepository;
use App\V1\Repositories\HandbookRepository;

class GroupSourceObserver
{
    /**
     * @var HandbookRepository
     */
    protected $handbooks;

    /**
     * @var InformationSourceRepository
     */
    protected $sources;

    /**
     * @param HandbookRepository $handbooks
     */
    public function __construct(HandbookRepository $handbooks,
        InformationSourceRepository $sources)
    {
        $this->handbooks = $handbooks;
        $this->sources = $sources;
    }

    /**
     * Add clinic in information sources after create clinic
     *
     * @param Clinic $clinic
     *
     * @return void
     */
    public function created(Clinic $clinic)
    {
        if ($clinic->group_id !== null) {
            $mediaType = $this->handbooks->getByServiceKey(Handbook::CATEGORY_MEDIA_TYPE, 'recommendation');
            if ($mediaType !== null) {
                $this->attachToSourceGroup($clinic->id, $clinic->group_id, $mediaType->id);
            }
        }
    }

    /**
     * Add/delete clinic in information sources after update clinic
     *
     * @param Clinic $clinic
     *
     * @return void
     */
    public function updated(Clinic $clinic)
    {
        if ($clinic->isDirty('group_id')) {
            $mediaType = $this->handbooks->getByServiceKey(Handbook::CATEGORY_MEDIA_TYPE, 'recommendation');
            if ($mediaType !== null) {
                $oldGroupId = $clinic->getOriginal('group_id');
                if ($oldGroupId) {
                    $this->detachFromSourceGroup($clinic->id, $oldGroupId, $mediaType->id);
                }
                $newGroupId = $clinic->group_id;
                if ($newGroupId) {
                    $this->attachToSourceGroup($clinic->id, $newGroupId, $mediaType->id);
                }
            }
        }
    }

    /**
     * Attach clinic to group source
     *
     * @param int $clinicId
     * @param int $groupId
     * @param int $mediaTypeId
     */
    protected function attachToSourceGroup($clinicId, $groupId, $mediaTypeId)
    {
        $sources = $this->sources->getSourcesByClinicGroup($groupId, $mediaTypeId);
        foreach ($sources as $source) {
            $source->clinics()->attach($clinicId);
        }
    }

    /**
     * Deach clinic from group source
     *
     * @param int $clinicId
     * @param int $groupId
     * @param int $mediaTypeId
     */
    protected function detachFromSourceGroup($clinicId, $groupId, $mediaTypeId)
    {
        $sources = $this->sources->getSourcesByClinicGroup($groupId, $mediaTypeId);
        foreach ($sources as $source) {
            $source->clinics()->detach($clinicId);
        }
    }
}
