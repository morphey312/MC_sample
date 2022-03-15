<?php

namespace App\V1\Console\Commands\OneS\Export;

use Illuminate\Console\Command;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Exception;
use App\V1\Models\Employee;
use App\V1\Models\Clinic;
use App\V1\Models\Clinic\MoneyReciever;
use App\V1\Traits\Services\OneS\ResponseProcess;

class Employees extends Command
{
    use ResponseProcess;

    const MONEY_RECIEVER = 'money_reciever';
    
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'export:employee_one_s {type} {--position=*}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Export employees by type [doctor, medicine, clinic, money_reciever] with specifying positions[2, 7, 9, 48, 50, 51]';

    /**
     * \GuzzleHttp\Client
     */
    protected $client;

    /**
     * @var array
     */
    protected $positions = [];

    /**
     * array of available commands
     */
    protected $commands = [
        'doctor' => 'send_doctors',
        'medicine' => 'send_medicines',
        'clinic' => 'send_clinics',
        'money_reciever' => 'send_money_recievers',
    ];

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $type = $this->argument('type');

        if (!isset($this->commands[$type])) {
            echo 'Unavailable command, try again';
            return;
        }

        $this->makeClient($type);

        $command = $this->commands[$type];
        $this->positions = $this->option('position');

        try {
            $data = $this->getData($type);
            
            if ($data->isNotEmpty()) {
                $request = $this->createTransferRequest($data, $command);
                $response = $this->client->send($request);
                $code = (int)$response->getStatusCode();
                if ($code === $this->codeSuccess) {
                    $this->info('Submition finished');
                    return;
                } else {
                    $this->error($this->getDecodedBody($response));
                    return;
                }
            }
            $this->info('Nothing to send');
            return;
        } catch (RequestException $e) {
            return $this->error($e->getResponse());
        } catch (Exception $e) {
            return $this->error($e->getMessage());
        }
    }

    /**
     * Make GuzzleHttp\Client
     * 
     * @param string $type
     */
    protected function makeClient($type = '')
    {
        $url = ($type == static::MONEY_RECIEVER) ? config('services.one_s.transaction') : config('services.one_s.issue');
        $this->client = new Client($url);
    }

    /**
     * Get export request data
     * 
     * @param string $type
     * 
     * @return collection
     */
    protected function getData($type)
    {
        switch ($type) {
            case 'doctor':
                return $this->getDoctors();
            case 'medicine':
                return $this->getMedicineIssuers();
            case 'clinic':
                return $this->getClinics();
            case static::MONEY_RECIEVER:
                return $this->getMoneyRecievers();
            default: 
                echo 'Nothing to export';
                return collect();
        }
    }

    /**
     * Get employee list which are doctors
     * 
     * @return collection
     */
    protected function getDoctors()
    {
        $doctors = Employee::whereHas('employee_clinics.position', function ($query) {
            $query->where($query->qualifyColumn('is_doctor'), 1);
        })->get();

        return $doctors->map(function($doctor) {
            return [
                'id' => $doctor->id,
                'full_name' => $doctor->full_name,
                'is_doctor' => true,
            ];
        });
    }

    /**
     * Get employee list which are medicine issuers
     * 
     * @return collection
     */
    protected function getMedicineIssuers()
    {
        $employees = Employee::whereHas('employee_clinics', function ($query) {
            $query->whereIn($query->qualifyColumn('position_id'), $this->positions);
        })->get();
        
        return $employees->map(function($employee) {
            return [
                'id' => $employee->id,
                'full_name' => $employee->full_name,
            ];
        });
    }

    /**
     * Get clinic list
     * 
     * @return collection
     */
    protected function getClinics()
    {
        $clinics = Clinic::get();
        return $clinics->map(function($clinic) {
            return [
                'id' => $clinic->id,
                'name' => $clinic->name,
                'official_name' => $clinic->official_name,
            ];
        });
    }

    /**
     * Get money recievers list
     * 
     * @return collection
     */
    protected function getMoneyRecievers()
    {
        $recievers = MoneyReciever::get();
        return $recievers->map(function($reciever) {
            return [
                'id' => $reciever->id,
                'name' => $reciever->name,
            ];
        });
    }

    /**
     * Create command request
     * 
     * @param mixed $payments
     * 
     * @return HTTP request object
     */ 
    protected function createTransferRequest($data, $command)
    {
    	return $this->createRequest([
            'json' => [
                'Command' => $command,
                'data' => $data,
            ],
        ]);
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
}