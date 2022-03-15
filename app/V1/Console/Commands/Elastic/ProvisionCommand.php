<?php

namespace App\V1\Console\Commands\Elastic;

use App\V1\Contracts\Repositories\Appointment\ServiceRepository;
use App\V1\Contracts\Repositories\Call\CallLogRepository;
use Illuminate\Console\Command;
use App\V1\Contracts\Repositories\AppointmentRepository;
use App\V1\Jobs\Elastic\Provision\Appointment as AppointmentJob;
use App\V1\Jobs\Elastic\Provision\CallLog as CallLogJob;
use App\V1\Jobs\Elastic\Provision\Service as AppointmentServiceJob;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Arr;
use Carbon\Carbon;
use Exception;

class ProvisionCommand extends Command
{
    const IDLE_TIME = 60;
    const OFFSET_TIME = 600;
    const CURSOR_KEY = '_ep_cursor';

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'elastic:provision {--immediate} {--watch} {--update-mapping} {--clinics=} {--date-from=} {--date-to=} {--entities=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Provision elastic with data';

    /**
     * @var bool
     */
    protected $shouldRun = true;

    /**
     * @var bool
     */
    protected $paused = false;

    /**
     * @var AppointmentRepository
     */
    protected $appointments;

    /**
     * @var CallLogRepository
     */
    protected $callLogs;

    /**
     * @var array
     */
    protected $map;

    /**
     * @var string
     */
    protected $rangeCursor;

    /**
     * @var string
     */
    protected $cursorKey;

    /**
     * @var bool
     */
    protected $dispatchSync;

    /**
     * Constructor
     */
    public function __construct(
        AppointmentRepository $appointments,
        CallLogRepository $callLogs)
    {
        parent::__construct();
        $this->appointments = $appointments;
        $this->callLogs = $callLogs;
        $this->cursorKey = config('cache.prefix') . self::CURSOR_KEY;
        $this->map = [
            'appointments' => [
                'source' => $appointments,
                'job' => AppointmentJob::class,
            ],
            'call_logs' => [
                'source' => $callLogs,
                'job' => CallLogJob::class,
            ],
        ];
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $watch = $this->option('watch');
        $clinics = $this->option('clinics');
        $entities = $this->option('entities');
        $from = $this->option('date-from');
        $to = $this->option('date-to');
        $updateMapping = $this->option('update-mapping');
        $this->dispatchSync = $this->option('immediate');

        if (!empty($from)) {
            $from = Carbon::parse($from)->format('Y-m-d H:i:s');
        }

        if (!empty($to)) {
            $to = Carbon::parse($to)->format('Y-m-d H:i:s');
        }

        if (empty($clinics)) {
            $clinics = null;
        } else {
            $clinics = array_map('trim', explode(',', $clinics));
        }

        if (empty($entities)) {
            $entities = [
                'appointments',
                'call_logs',
            ];
        } else {
            $entities = array_map('trim', explode(',', $entities));
        }

        if (!$watch) {
            if (!$from) {
                $this->info('Option --date-from should be specified for immediate sending');
                return;
            }
            if (!$to) {
                $to = Carbon::now()->format('Y-m-d H:i:s');
            }
            $this->sendImmediate($from, $to, $clinics, $entities, $updateMapping);
        } else {
            $this->setupSignals();
            if (!empty($from)) {
                $this->watchFrom($from);
            }
            if ($updateMapping) {
                $this->warning('Option `--update-mapping` does not have effect in combination with `--watch`');
            }
            $this->info('Watching data changes...');
            while ($this->shouldRun) {
                if ($this->paused) {
                    sleep(1);
                    continue;
                }
                $this->watchChanges($clinics, $entities);
                sleep(self::IDLE_TIME);
            }
            $this->info('Shutting down...');
        }
    }

    /**
     * Send data that match params to elastic
     *
     * @param string $from
     * @param string  $to
     * @param array|null $clinics
     * @param array $entities
     * @param bool $updateMapping
     */
    protected function sendImmediate($from, $to, $clinics, $entities, $updateMapping)
    {
        foreach (Arr::only($this->map, $entities) as $entity) {
            $source = $entity['source'];
            $job = $entity['job'];
            $update = new \StdClass();
            $update->value = $updateMapping;
            $source
                ->skipAccessCheck(true)
                ->chunkCreatedAt($from, $to, $clinics, function($records) use($job, $update) {
                    $this->processRecords($job, $records->pluck('id')->all(), $update->value);
                    $update->value = false;
                });
        }
    }

    /**
     * Watch changes on data that match params to elastic
     *
     * @param array|null $clinics
     * @param array $entities
     */
    protected function watchChanges($clinics, $entities)
    {
        $from = $this->watchFrom();
        $to = $this->watchTo();
        $this->watchFrom($to);
        $this->info(sprintf('Checking interval [%s; %s)...', $from, $to));
        foreach (Arr::only($this->map, $entities) as $entity) {
            $source = $entity['source'];
            $job = $entity['job'];
            $source
                ->skipAccessCheck(true)
                ->chunkUpdatedAt($from, $to, $clinics, function($records) use($job) {
                    $this->processRecords($job, $records->pluck('id')->all());
                });
        }
    }

    /**
     * Create job to process records
     *
     * @param string $jobClass
     * @param array $ids
     */
    protected function processRecords($jobClass, $ids, $updateMapping = false)
    {
        $this->info(sprintf('Processing %s::handle([%s], %s)...', $jobClass, implode(', ', $ids), $updateMapping ? 'true' : 'false'));
        if ($this->dispatchSync) {
            $jobClass::dispatchNow($ids, $updateMapping);
            $this->info(sprintf('Processed %s::handle([%s], %s)...', $jobClass, implode(', ', $ids), $updateMapping ? 'true' : 'false'));
        } else {
            $jobClass::dispatch($ids, $updateMapping)->onQueue('elastic');
        }
    }

    /**
     * Get/set watching lower range
     *
     * @param $from string
     *
     * @return string
     */
    protected function watchFrom($from = null)
    {
        if ($from !== null) {
            $this->rangeCursor = $from;
            Redis::set($this->cursorKey, $from);
            return $from;
        }
        if ($this->rangeCursor === null) {
            if (Redis::exists($this->cursorKey)) {
                $this->rangeCursor = Redis::get($this->cursorKey);
            } else {
                return $this->watchFrom($this->watchTo());
            }
        }
        return $this->rangeCursor;
    }

    /**
     * Get watching upper range
     *
     * @return string
     */
    protected function watchTo()
    {
        return Carbon::now()->subSeconds(self::OFFSET_TIME)->format('Y-m-d H:i:s');
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
