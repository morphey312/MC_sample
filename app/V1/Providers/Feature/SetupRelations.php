<?php

namespace App\V1\Providers\Feature;

use Illuminate\Database\Eloquent\Relations\Relation;
use App\V1\Repositories\Relations\FileAttachment as FileAttachmentRelation;
use App\V1\Repositories\Relations\Processor\FileAttachment as FileAttachmentProcessor;
use App\V1\Repositories\Relations\BelongsToBidirect as BelongsToBidirectRelation;
use App\V1\Repositories\Relations\Processor\BelongsToBidirect as BelongsToBidirectProcessor;
use App\V1\Repositories\Relations\HasOneBidirect as HasOneBidirectRelation;
use App\V1\Repositories\Relations\Processor\HasOneBidirect as HasOneBidirectProcessor;
use App\V1\Repositories\Relations\HasManyBidirect as HasManyBidirectRelation;
use App\V1\Repositories\Relations\Processor\HasManyBidirect as HasManyBidirectProcessor;
use Masterfri\SmartRelations\RelationsManager;

class SetupRelations
{
    /**
     * Boot feature
     *
     * @param \Illuminate\Contracts\Foundation\Application|\Illuminate\Foundation\Application $app
     */
    public static function boot($app)
    {
        Relation::morphMap([
            \App\V1\Models\Employee::RELATION_TYPE => \App\V1\Models\Employee::class,
            \App\V1\Models\Patient::RELATION_TYPE => \App\V1\Models\Patient::class,
            \App\V1\Models\Patient\Card\OutpatientRecord::RELATION_TYPE => \App\V1\Models\Patient\Card\OutpatientRecord::class,
            \App\V1\Models\Patient\Card\OutclinicProtocolRecord::RELATION_TYPE => \App\V1\Models\Patient\Card\OutclinicProtocolRecord::class,
            \App\V1\Models\Patient\Card\DiaryRecord::RELATION_TYPE => \App\V1\Models\Patient\Card\DiaryRecord::class,
            \App\V1\Models\Patient\Card\ServiceRecord::RELATION_TYPE => \App\V1\Models\Patient\Card\ServiceRecord::class,
            \App\V1\Models\Patient\Card\ConditionRecord::RELATION_TYPE => \App\V1\Models\Patient\Card\ConditionRecord::class,
            \App\V1\Models\Patient\Card\ProtocolRecord::RELATION_TYPE => \App\V1\Models\Patient\Card\ProtocolRecord::class,
            \App\V1\Models\Patient\Card\Assignment::RELATION_TYPE => \App\V1\Models\Patient\Card\Assignment::class,
            \App\V1\Models\Patient\Card\TreatmentAssignment::RELATION_TYPE => \App\V1\Models\Patient\Card\TreatmentAssignment::class,
            \App\V1\Models\Patient\Card\ConsultationRecord::RELATION_TYPE => \App\V1\Models\Patient\Card\ConsultationRecord::class,
            \App\V1\Models\Patient\Card\ManipulationRecord::RELATION_TYPE => \App\V1\Models\Patient\Card\ManipulationRecord::class,
            \App\V1\Models\Patient\Upload::RELATION_TYPE => \App\V1\Models\Patient\Upload::class,
            \App\V1\Models\Patient\Card\Document::RELATION_TYPE => \App\V1\Models\Patient\Card\Document::class,
            \App\V1\Models\Patient\Card\NextVisit::RELATION_TYPE => \App\V1\Models\Patient\Card\NextVisit::class,
            \App\V1\Models\Patient\Card\PrintedDocument::RELATION_TYPE => \App\V1\Models\Patient\Card\PrintedDocument::class,
            \App\V1\Models\Role::RELATION_TYPE => \App\V1\Models\Role::class,
            \App\V1\Models\Patient\Card\ArchiveNumber::RELATION_TYPE => \App\V1\Models\Patient\Card\ArchiveNumber::class,
            \App\V1\Models\Service::RELATION_TYPE => \App\V1\Models\Service::class,
            \App\V1\Models\Appointment\Service::RELATION_TYPE => \App\V1\Models\Appointment\Service::class,
            \App\V1\Models\Analysis::RELATION_TYPE => \App\V1\Models\Analysis::class,
            \App\V1\Models\Call::RELATION_TYPE => \App\V1\Models\Call::class,
            \App\V1\Models\Specialization::RELATION_TYPE => \App\V1\Models\Specialization::class,
            \App\V1\Models\Appointment::RELATION_TYPE => \App\V1\Models\Appointment::class,
            \App\V1\Models\Call\CallLog::RELATION_TYPE => \App\V1\Models\Call\CallLog::class,
            \App\V1\Models\Workspace::RELATION_TYPE => \App\V1\Models\Workspace::class,
            \App\V1\Models\Workspace\Clinic::RELATION_TYPE => \App\V1\Models\Workspace\Clinic::class,
            \App\V1\Models\PersonalTask::RELATION_TYPE => \App\V1\Models\PersonalTask::class,
            \App\V1\Models\Analysis\Result::RELATION_TYPE => \App\V1\Models\Analysis\Result::class,
            \App\V1\Models\DiscountCardType\Icon::RELATION_TYPE => \App\V1\Models\DiscountCardType\Icon::class,
            \App\V1\Models\Patient\SignalRecord::RELATION_TYPE => \App\V1\Models\Patient\SignalRecord::class,
            \App\V1\Models\Patient\AssignedMedicine::RELATION_TYPE => \App\V1\Models\Patient\AssignedMedicine::class,
            \App\V1\Models\DaySheet::RELATION_TYPE => \App\V1\Models\DaySheet::class,
            \App\V1\Models\Price::RELATION_TYPE => \App\V1\Models\Price::class,
            \App\V1\Models\PriceAgreementAct::RELATION_TYPE => \App\V1\Models\PriceAgreementAct::class,
            \App\V1\Models\CallRequest::RELATION_TYPE => \App\V1\Models\CallRequest::class,
            \App\V1\Models\Employee\OutclinicMedicine::RELATION_TYPE => \App\V1\Models\Employee\OutclinicMedicine::class,
            \App\V1\Models\Medicine::RELATION_TYPE => \App\V1\Models\Medicine::class,
            \App\V1\Models\Clinic\Blank::RELATION_TYPE => \App\V1\Models\Clinic\Blank::class,
            \App\V1\Models\Payment::RELATION_TYPE => \App\V1\Models\Payment::class,
            \App\V1\Models\InsuranceCompany::RELATION_TYPE => \App\V1\Models\InsuranceCompany::class,
            \App\V1\Models\Patient\IssuedMedicine::RELATION_TYPE => \App\V1\Models\Patient\IssuedMedicine::class,
            \App\V1\Models\InsuranceCompany\Act::RELATION_TYPE => \App\V1\Models\InsuranceCompany\Act::class,
            \App\V1\Models\Employee\DoctorIncomePlan::RELATION_TYPE => \App\V1\Models\Employee\DoctorIncomePlan::class,
            \App\V1\Models\EmailLog::RELATION_TYPE => \App\V1\Models\EmailLog::class,
            \App\V1\Models\Msp::RELATION_TYPE => \App\V1\Models\Msp::class,
            \App\V1\Models\Msp\Contract::RELATION_TYPE => \App\V1\Models\Msp\Contract::class,
            \App\V1\Models\Clinic::RELATION_TYPE => \App\V1\Models\Clinic::class,
            \App\V1\Models\Clinic\ServiceType::RELATION_TYPE => \App\V1\Models\Clinic\ServiceType::class,
            \App\V1\Models\Employee\ServiceType::RELATION_TYPE => \App\V1\Models\Employee\ServiceType::class,
            \App\V1\Models\TreatmentCourse\Document::RELATION_TYPE => \App\V1\Models\TreatmentCourse\Document::class,
            \App\V1\Models\Clinic\MoneyReciever::RELATION_TYPE => \App\V1\Models\Clinic\MoneyReciever::class,
            \App\V1\Models\Clinic\Group::RELATION_TYPE => \App\V1\Models\Clinic\Group::class,
            \App\V1\Models\DaySheet\LockLog::RELATION_TYPE => \App\V1\Models\DaySheet\LockLog::class,
            \App\V1\Models\Patient\InformationSource::RELATION_TYPE => \App\V1\Models\Patient\InformationSource::class,
            \App\V1\Models\Appointment\VideoSession::RELATION_TYPE => \App\V1\Models\Appointment\VideoSession::class,
            \App\V1\Models\PaymentMethod::RELATION_TYPE => \App\V1\Models\PaymentMethod::class,
            \App\V1\Models\SiteEnquiry::RELATION_TYPE => \App\V1\Models\SiteEnquiry::class,
            \App\V1\Models\Patient\Card\Record::RELATION_TYPE => \App\V1\Models\Patient\Card\Record::class,
            \App\V1\Models\Analysis\Laboratory\TransferSheet::RELATION_TYPE => \App\V1\Models\Analysis\Laboratory\TransferSheet::class,
            \App\V1\Models\AmbulanceCall::RELATION_TYPE => \App\V1\Models\AmbulanceCall::class,
            \App\V1\Models\Ehealth\Patient::RELATION_TYPE => \App\V1\Models\Ehealth\Patient::class,
            \App\V1\Models\Ehealth\Patient\Authentication::RELATION_TYPE => \App\V1\Models\Ehealth\Patient\Authentication::class,
            \App\V1\Models\Ehealth\Patient\ConfidantPerson::RELATION_TYPE => \App\V1\Models\Ehealth\Patient\ConfidantPerson::class,
        ]);

        RelationsManager::extend(FileAttachmentRelation::class, FileAttachmentProcessor::class);
        RelationsManager::extend(BelongsToBidirectRelation::class, BelongsToBidirectProcessor::class);
        RelationsManager::extend(HasOneBidirectRelation::class, HasOneBidirectProcessor::class);
        RelationsManager::extend(HasManyBidirectRelation::class, HasManyBidirectProcessor::class);
    }
}
