<?php

namespace App\V1\Services\OneS;

use App\V1\Contracts\Services\OneS\MedicineIssueService as MedicineIssueServiceInterface;
use App\V1\Models\User;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use App\V1\Traits\Services\OneS\ResponseProcess;
use Illuminate\Support\Facades\Log;
use Exception;
use DB;
use App\V1\Models\Employee;

class MedicineIssueService implements MedicineIssueServiceInterface
{
    use ResponseProcess;

    /**
     * @var string log channel
     */
    protected $logChannel = 'medicineIssueLog';

    /**
     * @var
     */
    protected $document;

    /**
     * @var string command name
     */
    protected $command = 'issue_medicine';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->client = new Client(config('services.one_s.issue'));
    }

    /**
     * Execute http request to 1c
     *
     * @param mixed $list
     */
    public function execute($service, $user, $document = null)
    {
        $this->document = $document;
        try {
            $medicines = $service->getAssignedMedicineItems(['service.medicine']);
            Log::channel($this->logChannel)->info("Sending document(" . $this->document->id . ") - id: [" . implode(', ', $medicines->pluck('service_id')->all()) . "]");
            $data = $this->prepare($service, $medicines, $user);
            $request = $this->createTransferRequest($data);
            $response = $this->client->send($request);
            $code = (int)$response->getStatusCode();
            if ($this->codeSuccess === $code) {
                $results = $this->getDecodedBody($response);
                $this->updateRests($results);
                return Log::channel($this->logChannel)->info("Transfer document(" . $this->document->id . ") finished: " . $this->getResultOutput($results));
            }
            return Log::channel($this->logChannel)->info($this->responseMessage($response));
        } catch (RequestException $e) {
            return Log::channel($this->logChannel)->info($this->responseMessage($e->getResponse()));
        } catch (Exception $e) {
            return Log::channel($this->logChannel)->info($this->responseMessage($e->getMessage()));
        }
    }

    protected function updateRests($results)
    {
        if (empty($results['rests'])) {
            return;
        }

        foreach ($results['rests'] as $rest) {
            DB::table('medicine_store_rests')
                ->whereRaw('store_id = (' . DB::raw("select id from medicine_stores where store_uid = '" . $rest['store'] . "' limit 1)"))
                ->whereRaw('medicine_id = (' . DB::raw("select id from medicines where new_medicine_uid = '" . $rest['item'] . "' limit 1)"))
                ->update([
                    'rest' => $rest['rest'],
                    'cost' => $rest['sell_price'],
                    'self_cost' => empty($rest['cost_price']) ? 0 : $rest['cost_price'],
                ]);
        }
    }

    protected function getResultOutput($results)
    {
        if (empty($results['doc_id'])) {
            return;
        }

        $output = 'doc_id [' . $results['doc_id'] . '] doc_num [' . $results['doc_num'] . ']';
        if (empty($results['rests'])) {
            return $output . ' rests are empty';
        }

        $medicines = [];
        foreach ($results['rests'] as $rest) {
            $medicines[] = 'store - ' . $rest['store'] . ': medicine - ' . $rest['item'] . ', rest - ' . $rest['rest'];
        }
        return $output . ' ' . implode('; ', $medicines);
    }

    /**
     * Get response error message
     *
     * @param Http $response
     *
     * @return string
     */
    protected function responseMessage($response)
    {
        if (is_string($response)) {
            return "Medicine issue document(" . $this->document->id . ") sending failed. " . $response;
        }
    	return "Medicine issue document(" . $this->document->id . ") sending failed. " . $response->getEffectiveUrl() . " " . $response->getStatusCode() . "/" . $response->getReasonPhrase();
    }

    /**
     * Create command request
     *
     * @param mixed $payments
     *
     * @return HTTP request object
     */
    protected function createTransferRequest($data)
    {
    	return $this->createRequest([
            'json' => [
                'Command' => $this->command,
                'data' => $data,
            ],
        ]);
    }

    /*
    Дата,
    Номер,
    Id  в медцентре,
    Ид пользователя медцентра,
    Имя пользователя медцентра,
    Ид организации,
    Название организации,
    Ид пациента,
    номер карты пациента.
    Массив товаров-табличная часть (
        items: (id товара, название товара (на всяк случай), количество списанного).
    */
    protected function prepare($service, $medicines, $user = null)
    {
        $items = $medicines->map(function($item) {
            $medicine = $item->service->medicine;
            return [
                'id' => $medicine->new_medicine_uid,
                'name' => $medicine->name,
                'quantity' => $item->quantity,
            ];
        })->toArray();

        $medicineWithSpecialization = $medicines->first(function($item) {
            return $item->service->card_specialization_id != null;
        });

        $assignerId = $service->assigner_id;
        $assigner = $assignerId ? Employee::find($assignerId) : null;

        return [
            'doc_id' => $this->document->id,
            'date' => $this->document->created_at,
            'user_id' => $user->id,
            'user_name' => $user->full_name,
            'clinic_id' => $service->clinic_id,
            'clinic_name' => $service->clinic->name,
            'is_doctor' => $assigner ? $assigner->isDoctor() : false,
            'doctor_id' => $assignerId,
            'doctor_name' => $assigner ? $assigner->full_name : '',
            'patient_id' => $service->patient_id,
            'patient_name' => $service->patient->full_name,
            'patient_card' => $medicineWithSpecialization ?
                            $service->patient->getCardNumber($service->clinic_id, $medicineWithSpecialization->service->card_specialization_id) : null,
            'items' => $items,
            'roles' => $this->getUserRoles($user),
        ];
    }

    /**
     * Create HTTP request
     *
     * @param array  $options
     * @param string $method
     * @param string $url
     *
     * @return HTTP request object
     */
    protected function createRequest($options = [], $method = 'POST', $url = '')
    {
        return $this->client->createRequest($method, $url, $options);
    }

    /**
     * Get user roles by employee model
     *
     * @param $employee
     * @return array
     */
    private function getUserRoles($employee): array
    {
        $user = User::where('userable_id', $employee->id)->first();

        if ($user) {
            return $user->getRoles()->map(function($role) {
                return [
                    'id' => $role->id,
                    'name' => $role->name,
                ];
            })->toArray();
        }

        return [];
    }
}
