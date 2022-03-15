<?php

namespace App\V1\Models;

use App\V1\Contracts\Services\Permissions\ClinicShared;
use App\V1\Models\BaseModel;
use App\V1\Traits\Permissions\SharedResource;
use App\V1\Contracts\Services\Permissions\SharedResource as SharedResourceInterface;
use App\V1\Traits\Models\HasConstraint;
use Illuminate\Support\Arr;

class Specialization extends BaseModel implements SharedResourceInterface, ClinicShared
{
    use SharedResource, HasConstraint;

    const APPOINTMENT_LIMITATION_DEFAULT = 10;
    const SERVICE_GROUP_SURGERY = 'surgery';

    const RELATION_TYPE = 'specialization';

    /**
     * @var array
     */
    protected $fillable = [
        'name',
        'name_lc1',
        'name_lc2',
        'name_lc3',
        'genitive_name',
        'short_name',
        'card_template_id',
        'course_days',
        'days_since',
        'not_use_for_new_patient_call',
        'is_non_profile_patient',
        'is_check_up',
        'is_non_treatment',
        'not_show_signal_records',
        'status',
        'adjacent_specializations',
        'service_group',
        'order',
        'online_appointment',
        'once_in_report',
        'additional_templates',
        'is_real_time_appointment',
    ];

    /**
     * @var array
     */
    protected $casts = [
        'not_use_for_new_patient_call' => 'boolean',
        'is_non_profile_patient' => 'boolean',
        'not_show_signal_records' => 'boolean',
        'is_non_treatment' => 'boolean',
        'is_check_up' => 'boolean',
        'online_appointment' => 'boolean',
        'once_in_report' => 'boolean',
        'is_real_time_appointment' => 'boolean',
    ];

    /**
     * @var array
     */
    protected $deleting_constraints = [
        'clinics',
        'calls',
        'time_sheets',
        'services',
        'call_requests',
        'adjacent_specializations',
        'appointments',
        'appointment_limitations',
        'personal_tasks',
        'protocol_templates',
    ];

    /**
     * Related call requests
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function call_requests()
    {
        return $this->hasMany(CallRequest::class, 'specialization_id');
    }

    /**
     * Related calls
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function calls()
    {
        return $this->hasMany(Call::class, 'specialization_id');
    }

    /**
     * Related personal tasks
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function personal_tasks()
    {
        return $this->hasMany(PersonalTask::class, 'specialization_id');
    }

    /**
     * Related clinics
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function clinics()
    {
        return $this->belongsToMany(Clinic::class, 'clinic_specialization', 'specialization_id', 'clinic_id')
            ->withPivot(
                'id',
                'first_patient_appointment_limit',
                'status',
                'days_since_last_visit',
                'show_days_since_message',
                'money_reciever_id'
            )->orderBy('name');
    }

    /**
     * Related specialization clinics
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function specialization_clinics()
    {
        return $this->hasMany(Specialization\Clinic::class, 'specialization_id');
    }

    /**
     * Related employee clinics
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function employee_clinics()
    {
        return $this->belongsToMany(Employee\Clinic::class, 'employee_specialization', 'specialization_id', 'employee_clinic_id');
    }

    /**
     * Related workspace clinics
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function workspace_clinics()
    {
        return $this->belongsToMany(Workspace\Clinic::class, 'workspace_specialization', 'specialization_id', 'workspace_clinic_id');
    }

    /**
     * Related time sheets
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function time_sheets()
    {
        return $this->belongsToMany(DaySheet\TimeSheet::class, 'specialization_time_sheet', 'specialization_id', 'time_sheet_id');
    }

    /**
     * Get related time sheet workspace data
     *
     * @param int $time_sheet_id
     *
     * @return array
     */
    public function time_sheet_workspace($time_sheet_id)
    {

        if (!is_null($time_sheet_id) && $workspace = $this->get_time_sheet_workspace($time_sheet_id)) {
            return [
                'id' => $workspace->id,
                'name' => $workspace->name,
            ];
        }

        return [];
    }

    /**
     * Get related time sheet workspace by time_sheet id
     *
     * @param int $time_sheet_id
     *
     * @return workspace
     */
    public function get_time_sheet_workspace($time_sheet_id)
    {
        return $this->time_sheets()
            ->find($time_sheet_id)
            ->workspaces()
            ->first();
    }

    /**
     * Related specializations
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function adjacent_specializations()
    {
        return $this->belongsToMany(Specialization::class, 'adjacent_specializations', 'specialization_id', 'adjacent_id')
                    ->withPivot('clinic_id');
    }

    /**
     * Related appointments
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function appointments()
    {
        return $this->hasMany(Appointment::class, 'specialization_id');
    }

    /**
     * Related appointment limitations
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function appointment_limitations()
    {
        return $this->hasMany(Appointment\Limitation::class, 'specialization_id');
    }

    /**
     * Detaching related adjacent specializations
     */
    public function detach_adjacent_specializations()
    {
        $this->adjacent_specializations()->detach();
    }

    /**
     * Attaching related adjacent specializations
     *
     * @param array $data
     */
    public function attach_adjacent_specializations($data = array())
    {
        if (!empty($data)) {
            foreach($data as $clinic => $list){
                $specializations = [];

                foreach($list as $key => $specialization) {
                    $specializations[$specialization] = [
                        'clinic_id' => $clinic
                    ];
                }

                $this->adjacent_specializations()->attach($specializations);
            }
        }
    }

    /**
     * Related services
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function services()
    {
        return $this->hasMany(Service::class, 'specialization_id');
    }

    /**
     * Related protocol templates
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function protocol_templates()
    {
        return $this->hasMany(Patient\Card\ProtocolTemplate::class, 'specialization_id');
    }

    /**
     * Related patient card specializations
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function patient_cards()
    {
        return $this->hasMany(Patient\Card\Specialization::class, 'specialization_id');
    }

    /**
     * Get once apperance list in report e.g. redirects
     *
     * @return collection
     */
    public static function getOnceApperanceReportList()
    {
        return static::where('once_in_report', '=', 1)->get();
    }

    /**
     * Related patient card specializations
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function money_recievers()
    {
        return $this->belongsToMany(Clinic\MoneyReciever::class, 'clinic_specialization', 'specialization_id', 'money_reciever_id')
            ->withPivot('clinic_id');
    }

    /**
     * Related patient card record templates
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function additional_templates()
    {
        return $this->belongsToMany(Patient\Card\RecordTemplate::class, 'specialization_record_template', 'specialization_id', 'card_template_id');
    }

    public function getClinicIds()
    {
        return $this->clinics->pluck('id')->all();
    }
}
