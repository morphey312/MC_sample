<?php

namespace App\V1\Traits\Resources\Patient\Card;

use App\V1\Http\Resources\BaseResource;
use App\V1\Http\Resources\Patient\Card\AssignmentResource;
use App\V1\Http\Resources\Patient\Card\ConsultationRecordResource;
use App\V1\Http\Resources\Patient\Card\DiaryRecordResource;
use App\V1\Http\Resources\Patient\Card\ManipulationRecordResource;
use App\V1\Http\Resources\Patient\Card\DocumentResource;
use App\V1\Http\Resources\Patient\Card\NextVisitResource;
use App\V1\Http\Resources\Patient\Card\OutclinicProtocolRecordResource;
use App\V1\Http\Resources\Patient\Card\OutpatientRecordHistoryResource;
use App\V1\Http\Resources\Patient\Card\OutpatientRecordResource;
use App\V1\Http\Resources\Patient\Card\ProtocolRecordResource;
use App\V1\Http\Resources\Patient\Card\ServiceRecordResource;
use App\V1\Http\Resources\Patient\Card\ConditionRecordResource;
use App\V1\Http\Resources\Patient\Card\TreatmentAssignmentHistoryResource;
use App\V1\Http\Resources\Patient\Card\TreatmentAssignmentResource;
use App\V1\Http\Resources\PrintedDocumentResource;
use App\V1\Models\Patient\Card\Assignment;
use App\V1\Models\Patient\Card\BaseRecordable;
use App\V1\Models\Patient\Card\ConsultationRecord;
use App\V1\Models\Patient\Card\DiaryRecord;
use App\V1\Models\Patient\Card\Document as PatientDocument;
use App\V1\Models\Patient\Card\ManipulationRecord;
use App\V1\Models\Patient\Card\NextVisit as NextVisit;
use App\V1\Models\Patient\Card\OutclinicProtocolRecord;
use App\V1\Models\Patient\Card\OutpatientRecord;
use App\V1\Models\Patient\Card\PrintedDocument;
use App\V1\Models\Patient\Card\ProtocolRecord;
use App\V1\Models\Patient\Card\ServiceRecord;
use App\V1\Models\Patient\Card\ConditionRecord;
use App\V1\Models\Patient\Card\TreatmentAssignment;

trait Recordable
{
    public $history;

    /**
     * Preload nested relation on recordable records
     *
     * @param \Illuminate\Support\Collection $recordables
     */
    protected function preloadRecordable($recordables)
    {
        $recordables->filter(function($recordable) {
            return $recordable instanceof OutpatientRecord;
        })->loadMissing([
            'fields.field_template',
            'diagnosis_icd',
        ]);

        $recordables->filter(function($recordable) {
            return $recordable instanceof ProtocolRecord;
        })->loadMissing([
            'file',
            'template',
        ]);

        $recordables->filter(function($recordable) {
            return $recordable instanceof OutclinicProtocolRecord;
        })->loadMissing([
            'attachments',
        ]);

        $recordables->filter(function($recordable) {
            return $recordable instanceof TreatmentAssignment;
        })->loadMissing([
            'appointment_services.service.specialization',
        ]);

        $recordables->filter(function($recordable) {
            return $recordable instanceof ConsultationRecord;
        })->loadMissing([
            'consultations' => function($query) {
                $query->with(['specialization', 'outclinic_specialization']);
            },
        ]);

        $recordables->filter(function($recordable) {
            return $recordable instanceof PatientDocument;
        })->loadMissing([
            'doctor',
            'attachments',
        ]);

        $recordables->filter(function($recordable) {
            return $recordable instanceof PrintedDocument;
        })->loadMissing([
            'file',
        ]);
    }

    /**
     * Wrap recordable into proper resource
     *
     * @param BaseRecordable $recordable
     *
     * @return BaseResource|null
     */
    protected function wrapRecordable($recordable)
    {
        if ($recordable instanceof OutpatientRecord) {
            if ($this->history) {
                return new OutpatientRecordHistoryResource($recordable);
            }

            return new OutpatientRecordResource($recordable);
        }

        if ($recordable instanceof DiaryRecord) {
            return new DiaryRecordResource($recordable);
        }

        if ($recordable instanceof Assignment) {
            return new AssignmentResource($recordable);
        }

        if ($recordable instanceof ProtocolRecord) {
            return new ProtocolRecordResource($recordable);
        }

        if ($recordable instanceof OutclinicProtocolRecord) {
            return new OutclinicProtocolRecordResource($recordable);
        }

        if ($recordable instanceof TreatmentAssignment) {
            if ($this->history) {
                return new TreatmentAssignmentHistoryResource($recordable);
            }

            return new TreatmentAssignmentResource($recordable);
        }

        if ($recordable instanceof ConsultationRecord) {
            return new ConsultationRecordResource($recordable);
        }

        if ($recordable instanceof PatientDocument) {
            return new DocumentResource($recordable);
        }

        if ($recordable instanceof NextVisit) {
            return new NextVisitResource($recordable);
        }

        if ($recordable instanceof PrintedDocument) {
            return new PrintedDocumentResource($recordable);
        }

        if ($recordable instanceof ServiceRecord) {
            return new ServiceRecordResource($recordable);
        }

        if ($recordable instanceof ConditionRecord) {
            return new ConditionRecordResource($recordable);
        }

        if ($recordable instanceof ManipulationRecord) {
            return new ManipulationRecordResource($recordable);
        }
        return null;
    }
}
