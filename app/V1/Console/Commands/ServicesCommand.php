<?php

namespace App\V1\Console\Commands;

use Illuminate\Console\Command;

class ServicesCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'service {target : Service to control, "all" means all services} {action : Action, which is one of: "start", "stop" or "status"}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Start/stop/check necessary services';

    /**
     * @var array
     */
    protected $targets = [
        'voip-control' => [
            'workDir' => false,
            'start' => 'php artisan voip:control',
            'lockFile' => 'var/voip-control.lock',
            'writeLock' => true,
            'logs' => '/dev/null',
        ],
    ];

    /**
     * @var bool
     */
    protected $shouldRun = true;

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $target = $this->argument('target');
        $action = $this->argument('action');

        if (!in_array($action, ['start', 'stop', 'status', 'keep-alive'])) {
            $this->error('Unrecognized action: ' . $action);
            return;
        }

        if ($action === 'keep-alive') {
            if ($target === 'all') {
                $this->keepAlive($this->targets);
            } elseif (isset($this->targets[$target])) {
                $this->keepAlive([
                    $target => $this->targets[$target],
                ]);
            } else {
                $this->error('Unrecognized target: ' . $target);
                return;
            }
        } else {
            if ($target === 'all') {
                foreach ($this->targets as $key => $config) {
                    $this->$action($key, $config);
                }
            } elseif (isset($this->targets[$target])) {
                $this->$action($target, $this->targets[$target]);
            } else {
                $this->error('Unrecognized target: ' . $target);
                return;
            }
        }
    }

    /**
     * Keep processes alive
     *
     * @param array $targets
     */
    protected function keepAlive($targets)
    {
        $this->setupSignals();

        $this->info(sprintf('Keeping alive: %s', implode(', ', array_keys($targets))));

        while ($this->shouldRun) {
            foreach ($targets as $name => $config) {
                if (!$this->checkService($config['lockFile'])) {
                    $this->info('Service "' . $name . '" is NOT running.');
                    $this->start($name, $config, false);
                }
            }
            sleep(30);
        }

        $this->info('Keep alive is terminating');
    }

    /**
     * Start service
     *
     * @param string $name
     * @param array $config
     * @param bool $checkIsRunning
     */
    protected function start($name, array $config, $checkIsRunning = true)
    {
        if ($checkIsRunning && $this->checkService($config['lockFile'])) {
            $this->info('Service "' . $name . '" is already running.');
            return;
        }

        $cwd = getcwd();
        if ($config['workDir']) {
            chdir($config['workDir']);
        }

        $this->info('Starting service "' . $name . '" ...');
        $pid = $this->startProcess($config['start'], $config['logs']);

        if ($config['workDir']) {
            chdir($cwd);
        }

        if ($pid === 0) {
            $this->error('Unable to start process!');
            return;
        }

        if ($config['writeLock']) {
            $this->writePid($pid, $config['lockFile']);
        }

        $this->info('Service "' . $name . '" has been successfully started.');
    }

    /**
     * Stop service
     *
     * @param string $name
     * @param array $config
     */
    protected function stop($name, array $config)
    {
        if (!$this->checkService($config['lockFile'])) {
            $this->info('Service "' . $name . '" is not running.');
            return;
        }

        $this->info('Stopping service "' . $name . '" ...');
        $pid = $this->readPid($config['lockFile']);
        $success = $this->stopProcess($pid);

        if (!$success) {
            $this->error('Unable to stop process!');
            return;
        }

        $this->info('Service "' . $name . '" has been successfully stopped.');
    }

    /**
     * Display service status
     *
     * @param string $name
     * @param array $config
     */
    protected function status($name, array $config)
    {
        if ($this->checkService($config['lockFile'])) {
            $this->info('Service "' . $name . '" is running.');
        } else {
            $this->info('Service "' . $name . '" is NOT running.');
        }
    }

    /**
     * Check if service is running
     *
     * @param string $lockFile
     *
     * @return bool
     */
    protected function checkService($lockFile)
    {
        $pid = $this->readPid($lockFile);
        if ($pid) {
            return $this->checkPid($pid);
        }
        return false;
    }

    /**
     * Check if process is running
     *
     * @param int $pid
     *
     * @return bool
     */
    protected function checkPid($pid)
    {
        $result = shell_exec(sprintf('ps h %d 2>&1', $pid));
        if (preg_match('/^\s*([0-9]+)/', $result, $match)) {
            return $pid == $match[1];
        }
        return false;
    }

    /**
     * Run process
     *
     * @param string $command
     * @param string $logfile
     *
     * @return int
     */
    protected function startProcess($command, $logfile)
    {
        return (int) shell_exec(sprintf('nohup %s >%s 2>&1 & echo $!', $command, $logfile));
    }

    /**
     * Stop process
     *
     * @param int $pid
     *
     * @return bool
     */
    protected function stopProcess($pid)
    {
        $result = shell_exec(sprintf('kill %d 2>&1', $pid));
        return stripos($result, 'No such process') === false;
    }

    /**
     * Write PID to lock file
     *
     * @param int $pid
     * @param string $lockFile
     */
    protected function writePid($pid, $lockFile)
    {
        $data = json_encode(['process' => $pid]);
        file_put_contents($lockFile, $data);
    }

    /**
     * Read PID from lock file
     *
     * @param string $lockFile
     *
     * @return int
     */
    protected function readPid($lockFile)
    {
        if (is_file($lockFile)) {
            $data = @json_decode(file_get_contents($lockFile), true);
            if ($data) {
                return (int) $data['process'];
            }
        }
        return 0;
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
        }
    }
}
