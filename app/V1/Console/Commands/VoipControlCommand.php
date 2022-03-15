<?php

namespace App\V1\Console\Commands;

use Illuminate\Console\Command;
use App\V1\Contracts\Services\Voip\MessageQueue;
use App\V1\Contracts\Services\VoipService;
use App\V1\Contracts\Repositories\Call\CallLogRepository;
use App\V1\Contracts\Repositories\ClinicRepository;
use App\V1\Contracts\Repositories\Employee\ClinicRepository as EmployeeClinicRepository;
use App\V1\Contracts\Services\Voip\SubResolver;
use Exception;

class VoipControlCommand extends Command
{
    use VoipControl\Concerns\HandleEvents,
        VoipControl\Concerns\ProcessMessages,
        VoipControl\Concerns\CallsPool,
        VoipControl\Concerns\Parkinglots,
        VoipControl\Concerns\Logging,
        VoipControl\Concerns\QueueMapping,
        VoipControl\Concerns\SipMapping;
    
    const IDLE_TIME = 200000;
    const CALLS_POOL_PREFIX = '_call_';
    const SIP_USER_PREFIX = 'sip_user_';
    const CALLS_POOL_TTL = 10800; // 3 hours
    const SIP_MAP_TTL = 28800; // 8 hrs
    const RECONNECT_DELAY = 5;
    
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'voip:control';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Start VoIP control process';
    
    /**
     * @var bool
     */
    protected $shouldRun = true;
    
    /**
     * @var bool
     */
    protected $paused = false;
    
    /**
     * @var MessageQueue
     */ 
    protected $messageQueue;
    
    /**
     * @var VoipService
     */ 
    protected $voipManager;
    
    /**
     * @var CallLogRepository
     */
    protected $callLogs;
    
    /**
     * @var ClinicRepository
     */
    protected $clinics;
    
    /**
     * @var EmployeeClinicRepository
     */
    protected $employeeClinics;
    
    /**
     * @var SubResolver
     */ 
    protected $subResolver;
    
    /**
     * @var \Illuminate\Support\Collection
     */
    protected $holdRequests;

    /**
     * @var string
     */
    protected $redisPrefixCalls;

    /**
     * Constructor
     * 
     * @param MessageQueue $messageQueue
     */ 
    public function __construct(
        VoipService $voipManager, 
        MessageQueue $messageQueue, 
        CallLogRepository $callLogs,
        ClinicRepository $clinics,
        EmployeeClinicRepository $employeeClinics,
        SubResolver $subResolver)
    {
        parent::__construct();
        $this->voipManager = $voipManager;
        $this->messageQueue = $messageQueue;
        $this->callLogs = $callLogs;
        $this->clinics = $clinics;
        $this->employeeClinics = $employeeClinics;
        $this->subResolver = $subResolver;
        $this->holdRequests = collect([]);
        $this->redisPrefixCalls = config('cache.prefix') . self::CALLS_POOL_PREFIX;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->setupSignals();
        
        $this->trace('Connecting to Asterisk server...');
        try {
            $this->voipManager->connect();
        } catch (Exception $e) {
            $this->logError('Unable to connect to Asterisk server');
            exit(1);
        }
        $this->trace('Connected. Waiting for events...');
        
        $this->setupListeners();
        $this->fetchParkingLots();
        $this->fetchClinicQueues();
        
        while ($this->shouldRun) {
            if ($this->paused) {
                $this->sleep();
                continue;
            }
            
            $message = $this->getNextMessage();
            if ($message !== null) {
                $this->processMessage($message);
            }
            
            $numEvents = $this->voipManager->process();
            
            if ($message === null && $numEvents === 0) {
                $this->sleep();
            }
        }
        
        $this->trace('Shutting down...');
        $this->voipManager->disconnect();
    }    
    
    /**
     * Sleeping...
     */ 
    protected function sleep()
    {
        usleep(self::IDLE_TIME);
    }
    
    /**
     * Setup pcntl signals
     */ 
    protected function setupSignals()
    {
        if (extension_loaded('pcntl')) {
            pcntl_async_signals(true);

            pcntl_signal(SIGTERM, function () {
                $this->shouldRun = false;
            });

            pcntl_signal(SIGUSR2, function () {
                $this->paused = true;
            });

            pcntl_signal(SIGCONT, function () {
                $this->paused = false;
            });
        }
    }
}
