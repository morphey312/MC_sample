<?php

namespace App\V1\Console\Commands\Ehealth;

use Illuminate\Console\Command;
use App\V1\Contracts\Services\EhealthService;
use App\V1\Contracts\Repositories\HandbookRepository;
use App\V1\Contracts\Repositories\Ehealth\PositionRepository;
use App\V1\Contracts\Repositories\Ehealth\Encounter\HandBook\ReasonRepository;
use App\V1\Contracts\Repositories\Ehealth\Encounter\HandBook\DischargeDepartmentRepository;
use App\V1\Contracts\Repositories\Ehealth\ServiceRepository;
use Illuminate\Support\Arr;
use Exception;
use Illuminate\Support\Facades\Log;
use App\V1\Models\Ehealth\Position;
use App\V1\Models\Handbook;
use App\V1\Models\User;
use App\V1\Models\Ehealth\Service;
use App\V1\Models\Ehealth\Encounter\DischargeDepartment;
use App\V1\Models\Ehealth\Encounter\Reason;
use Illuminate\Support\Facades\Auth;

class UpdateHandbooksCommand extends Command
{
    const ADMIN = 'admin';

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ehealth:handbooks-update';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update ehealth handbooks';

    /**
     * Execute the console command.
     *
     * @param EhealthService $ehealth
     *
     */
    public function handle(
        EhealthService $ehealth,
        HandbookRepository $handbook,
        PositionRepository $positions,
        ReasonRepository $reasons,
        DischargeDepartmentRepository $dischargeDepartments,
        ServiceRepository $services
    ) {
        try {
            $this->loginAsAdmin();

            $dicts = $ehealth->getDictionaries();
            foreach ($dicts['data'] as $category) {
                switch ($category['name']) {
                    case 'POSITION';
                        $this->updateEhealthDictionary($positions, $category['values'], Position::class);
                        break;
                    case 'eHealth/ICPC2/reasons';
                        $this->updateEhealthDictionary($reasons, $category['values'], Reason::class);
                        break;
                    case 'eHealth/encounter_discharge_department';
                        $this->updateEhealthDictionary($dischargeDepartments, $category['values'], DischargeDepartment::class);
                        break;
                    case 'eHealth/episode_types':
                        $this->updateHandbooks($handbook, 'ehealth_episode_type', $category['values']);
                        break;
                    case 'eHealth/episode_statuses':
                        $this->updateHandbooks($handbook, 'ehealth_episode_status', $category['values']);
                        break;
                    case 'eHealth/episode_closing_reasons':
                        $this->updateHandbooks($handbook, 'ehealth_episode_closing_reasons', $category['values']);
                        break;
                    case 'eHealth/cancellation_reasons':
                        $this->updateHandbooks($handbook, 'ehealth_cancellation_reasons', $category['values']);
                        break;
                    case 'eHealth/encounter_statuses':
                        $this->updateHandbooks($handbook, 'ehealth_encounter_statuses', $category['values']);
                        break;
                    case 'eHealth/encounter_classes':
                        $this->updateHandbooks($handbook, 'ehealth_encounter_classes', $category['values']);
                        break;
                    case 'eHealth/encounter_types':
                        $this->updateHandbooks($handbook, 'ehealth_encounter_types', $category['values']);
                        break;
                    case 'eHealth/encounter_priority':
                        $this->updateHandbooks($handbook, 'ehealth_encounter_priority', $category['values']);
                        break;
                    case 'eHealth/encounter_discharge_disposition':
                        $this->updateHandbooks($handbook, 'ehealth_encounter_discharge_disposition', $category['values']);
                        break;
                    case 'eHealth/encounter_re_admission':
                        $this->updateHandbooks($handbook, 'ehealth_encounter_re_admission', $category['values']);
                        break;
                    case 'eHealth/encounter_admit_source':
                        $this->updateHandbooks($handbook, 'ehealth_encounter_admit_source', $category['values']);
                        break;
                    case 'eHealth/condition_severities':
                        $this->updateHandbooks($handbook, 'ehealth_condition_severities', $category['values']);
                        break;
                    case 'eHealth/body_sites':
                        $this->updateHandbooks($handbook, 'ehealth_body_sites', $category['values']);
                        break;
                    case 'eHealth/condition_verification_statuses':
                        $this->updateHandbooks($handbook, 'ehealth_condition_verification_statuses', $category['values']);
                        break;
                    case 'eHealth/condition_clinical_statuses':
                        $this->updateHandbooks($handbook, 'ehealth_condition_clinical_statuses', $category['values']);
                        break;
                    case 'eHealth/diagnostic_report_categories':
                        $this->updateHandbooks($handbook, 'ehealth_diagnostic_report_categories', $category['values']);
                        break;
                    case 'eHealth/procedure_statuses':
                        $this->updateHandbooks($handbook, 'ehealth_procedure_statuses', $category['values']);
                        break;
                    case 'eHealth/diagnostic_report_statuses':
                        $this->updateHandbooks($handbook, 'ehealth_diagnostic_report_statuses', $category['values']);
                        break;
                    case 'eHealth/procedure_status_reasons':
                        $this->updateHandbooks($handbook, 'ehealth_procedure_status_reasons', $category['values']);
                        break;
                    case 'eHealth/procedure_categories':
                        $this->updateHandbooks($handbook, 'ehealth_procedure_categories', $category['values']);
                        break;
                    case 'eHealth/procedure_outcomes':
                        $this->updateHandbooks($handbook, 'ehealth_procedure_outcomes', $category['values']);
                        break;
                    case 'GENDER':
                        $this->updateHandbooks($handbook, 'ehealth_gender', $category['values']);
                        break;
                    case 'PHONE_TYPE':
                        $this->updateHandbooks($handbook, 'ehealth_phone_type', $category['values']);
                        break;
                    case 'AUTHENTICATION_METHOD':
                        $this->updateHandbooks($handbook, 'ehealth_authentication_method', $category['values']);
                        break;
                    case 'CONFIDANT_PERSON_TYPE':
                        $this->updateHandbooks($handbook, 'ehealth_confidant_person_type', $category['values']);
                        break;
                    case 'DOCUMENT_RELATIONSHIP_TYPE':
                        $this->updateHandbooks($handbook, 'ehealth_document_relationship_type', $category['values']);
                        break;
                    case 'DOCUMENT_TYPE':
                        $this->updateHandbooks($handbook, 'person_document', $category['values']);
                        break;
                }
            }

            $catalog = $ehealth->getServices();
            foreach ($catalog['data'] as $service) {
                $this->updateService($services, $service);
            }

            $this->logout();
        } catch (Exception $e) {
            Log::error($e->getMessage());
        }
    }

    /**
     * Login as admin
     */
    protected function loginAsAdmin()
    {
        $user = User::where('login', self::ADMIN)->first();
        if ($user === null) {
            throw new Exception('Can not login as admin user');
        }
        Auth::login($user);
    }

    /**
     * Logout
     */
    protected function logout()
    {
        Auth::logout();
    }

    /**
     * Update ehealth dictionary
     *
     * @param PositionRepository $positions
     * @param array $source
     */
    protected function updateEhealthDictionary($collections, $source, $model)
    {
        $collection = $collections->all()->keyBy('code');

        foreach ($source as $code => $name) {
            if ($collection->has($code)) {
                $position = $collection->get($code);
                $position->name = $name;
            } else {
                $position = new $model();
                $position->name = $name;
                $position->code = $code;
            }
            $collections->persist($position);
        }
    }

    /**
     * Update handbook category
     *
     * @param HandbookRepository $handbook
     * @param string $category
     * @param array $source
     */
    protected function updateHandbooks($handbook, $category, $source)
    {
        $filter = $handbook->filter(['category' => $category]);
        $collection = $handbook->all($filter)->filter(function($option) {
            return $option->key_ehealth !== null;
        })->keyBy('key_ehealth');

        foreach ($source as $code => $name) {
            if ($collection->has($code)) {
                $option = $collection->get($code);
                $option->value = $name;
            } else {
                $option = new Handbook();
                $option->category = $category;
                $option->key = strtolower($code);
                $option->key_ehealth = $code;
                $option->value = $name;
            }
            $handbook->persist($option);
        }
    }

    /**
     * Create/Update service
     *
     * @param ServiceRepository $services
     * @param array $data
     * @param array $parent
     */
    protected function updateService($services, $data, $parent = null)
    {
        $filter = $services->filter(['ehealth_id' => $data['id']]);
        $service = $services->get($filter, null, 1)->first();

        if ($service === null) {
            $service = new Service();
            $service->ehealth_id = $data['id'];
        }

        $service->code = $data['code'];
        $service->name = $data['name'];
        $service->request_allowed = $data['request_allowed'];
        $service->parent_id = $parent === null ? null : $parent->id;
        $service->category = empty($data['category']) ? null : $data['category'];
        $service->save();

        if (!empty($data['services'])) {
            foreach ($data['services'] as $child) {
                $this->updateService($services, $child, $service);
            }
        }
    }
}
