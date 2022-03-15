<?php

namespace App\V1\Models;

use App\V1\Models\BaseModel;
use App\V1\Traits\Permissions\SharedResource;
use App\V1\Contracts\Services\Permissions\SharedResource as SharedResourceInterface;

class Handbook extends BaseModel implements SharedResourceInterface
{
    use SharedResource;

    const CATEGORY_PATIENT_SOURCE = 'patient_source';
    const CATEGORY_BLACK_MARK_REASON = 'black_mark_reason';
    const CATEGORY_SKK_REASON = 'skk_reason';
    const CATEGORY_CURRENCY = 'currency';
    const CATEGORY_COUNTRY = 'country';
    const CATEGORY_CITY = 'city';
    const CATEGORY_ACTIVE_STATUS = 'active_status';
    const CATEGORY_EMPLOYEE_STATUS = 'employee_status';
    const CATEGORY_REASON_IMPOSSIBILITY_OF_CALL_PROCESSING = 'reason_impossibility_of_call_processing';
    const CATEGORY_CALL_PROCESS_STATUS = 'call_process_status';
    const CATEGORY_CALL_PROCESS_IS_PATIENT = 'call_process_is_patient';
    const CATEGORY_CALL_PROCESS_VISIT_TYPE = 'call_process_visit_type';
    const CATEGORY_PRICE_SET = 'price_set';
    const CATEGORY_SPECIALIZATION_PRIORITY = 'specialization_priority';
    const CATEGORY_PERSONAL_TASK_STATUS = 'personal_task_status';
    const CATEGORY_MEDIA_TYPE = 'media_type';
    const CATEGORY_ANALYSIS_RESULT_STATUS = 'analysis_status';
    const CATEGORY_ANALYSIS_FILTER_RESULT_STATUS = 'analysis_filter_status';
    const CATEGORY_CARD_NUMBERING_TYPE = 'card_numbering_type';
    const CATEGORY_BLOOD_GROUP = 'blood_group';
    const CATEGORY_RHESUS_FACTOR = 'rhesus_factor';
    const CATEGORY_DIABETES = 'diabetes';
    const CATEGORY_ADDITIONAL_SERVICE_MARK = 'additional_service_mark';
    const CATEGORY_SPECIALIZATION_SERVICE_GROUP = 'specialization_service_group';
    const CATEGORY_PAYMENT_TYPE = 'payment_type';
    const CATEGORY_PATIENT_RELATIVE = 'patient_relatives';
    const CATEGORY_ENQUIRY_TYPE = 'enquiry_type';
    const CATEGORY_ENQUIRY_PAYMENT_STATUS = 'enquiry_payment_status';
    const CATEGORY_REASON_REFUSING_TREATMENT = 'reason_refusing_treatment';
    const CATEGORY_VOIP_QUEUE = 'voip_queue';
    const CATEGORY_EMPLOYEE_SYSTEM_STATUS = 'employee_system_status';
    const CATEGORY_NOTIFICATION_CHANNEL_TYPE = 'channel_type';
    const CATEGORY_NOTIFICATION_SCENARIO = 'notification_scenario';

    const CATEGORY_NOTIFICATION_MAILING_PROVIDER_TYPE = 'mailing_provider_type';
    const CATEGORY_NOTIFICATION_MAILING_SCENARIO = 'notification_mailing_scenario';

    const CATEGORY_PATIENT_UPLOAD_TYPE = 'patient_upload_type';
    const CATEGORY_DOCTOR_INCOME_SERVICE_MARK = 'doctor_plan_service_mark';
    const CATEGORY_PERSON_DOCUMENT = 'person_document';
    const CATEGORY_EDUCATION_DEGREE = 'education_degree';
    const CATEGORY_EHEALTH_PHONE_TYPE = 'ehealth_phone_type';
    const CATEGORY_PREFERRED_WAY_COMMUNICATION = 'preferred_way_communication';
    const CATEGORY_EHEALTH_AUTHENTICATION_METHOD = 'ehealth_authentication_method';
    const CATEGORY_QUALIFICATION_TYPE = 'qualification_type';
    const CATEGORY_SPECIALITY_LEVEL = 'speciality_level';
    const CATEGORY_SPECIALITY_QUALY_TYPE = 'speciality_qualy_type';
    const CATEGORY_SCIENCE_DEGREE = 'science_degree';
    const CATEGORY_MSP_TYPE = 'msp_type';
    const CATEGORY_ACCREDITATION_CATEGORY = 'accreditation_category';
    const CATEGORY_LICENSE_TYPE = 'license_type';
    const CATEGORY_WAIT_LIST_RECORD_CANCEL_REASON = 'wait_list_record_cancel_reason';
    const CATEGORY_ENQUIRY_CATEGORIES = 'enquiry_categories';
    const CATEGORY_DEPARTMENT_TYPE = 'department_type';
    const CATEGORY_SURGERY_ROLE = 'surgery_role';
    const CATEGORY_CLINIC_KIND = 'clinic_kind';
    const CATEGORY_MSP_CONTRACT_TYPE = 'msp_contract_type';
    const CATEGORY_MSP_CONTRACT_FORM = 'msp_contract_form';
    const CATEGORY_SERVICE_PROVIDING_CONDITION = 'service_providing_conditions';
    const CATEGORY_SUBCONTRACTOR_SERVICE_TYPE = 'subcontrator_service_type';
    const CATEGORY_STATIONAR_MOZ_BLANK = 'stationar_moz_blank';
    const CATEGORY_CARE_EPISODE_TYPE = 'ehealth_episode_type';
    const CATEGORY_ENCOUNTER_TYPES = 'ehealth_encounter_types';
    const CATEGORY_ENCOUNTER_PRIORITY = 'ehealth_encounter_priority';
    const CATEGORY_ENCOUNTER_ADMIT_SOURCE = 'ehealth_encounter_admit_source';
    const CATEGORY_ENCOUNTER_RE_ADMISSIONS = 'ehealth_encounter_re_admission';
    const CATEGORY_ENCOUNTER_DISCHARGE_DISPOSITION = 'ehealth_encounter_discharge_disposition';
    const CATEGORY_CONDITION_CLINICAL_STATUSES = 'ehealth_condition_clinical_statuses';
    const CATEGORY_CONFIDANT_PERSON_TYPE = 'ehealth_confidant_person_type';
    const CATEGORY_DOCUMENT_RELATIONSHIP_TYPE = 'ehealth_document_relationship_type';
    const CATEGORY_CONDITION_VERIFICATION_STATUSES = 'ehealth_condition_verification_statuses';
    const CATEGORY_BODY_SITES = 'ehealth_body_sites';
    const CATEGORY_CONDITION_SEVERITIES = 'ehealth_condition_severities';
    const CATEGORY_EPISODE_CLOSING_REASONS = 'ehealth_episode_closing_reasons';
    const CATEGORY_CANCELLATION_REASONS = 'ehealth_cancellation_reasons';
    const CATEGORY_DIAGNOSTIC_REPORT_CATEGORIES = 'ehealth_diagnostic_report_categories';
    const CATEGORY_PROCEDURE_CATEGORIES = 'ehealth_procedure_categories';
    const CATEGORY_PROCEDURE_STATUS_REASONS = 'ehealth_procedure_status_reasons';
    const CATEGORY_PROCEDURE_OUTCOMES = 'ehealth_procedure_outcomes';
    const CATEGORY_PRICE_AGREEMENT_ACT_TYPE = 'price_agreement_act_type';
    const CATEGORY_PRICE_AGREEMENT_ACT_STATUS = 'price_agreement_act_status';
    const CATEGORY_PRICE_AGREEMENT_ACT_PRICES_CHANGE_TYPE = 'price_agreement_act_prices_change_type';

    /**
     * @var array
     */
    protected $fillable = [
        'value',
        'value_lc1',
        'value_lc2',
        'value_lc3',
        'key',
    ];

    /**
     * Get key value
     *
     * @param string|null $value
     *
     * @return string
     */
    public function getKeyAttribute($value)
    {
        return $value !== null ? $value : (string) $this->id;
    }

    /**
     * Set key value
     *
     * @param string|null $value
     *
     * @return string
     */
    public function setKeyAttribute($value)
    {
        $this->attributes['key'] = empty($value) ? null : $value;
    }
}
