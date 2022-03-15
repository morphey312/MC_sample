<?php

namespace App\V1\Logging;

use Monolog\Handler\RotatingFileHandler;
use Monolog\Handler\SyslogHandler;
use Monolog\Logger;

class MedicineImportLogger 
{
    /**
     * Create a custom Monolog instance.
     *
     * @param  array  $config
     * @return \Monolog\Logger
     */
    public function __invoke(array $config)
    {
        $logger = new Logger('MedicineImportLogger');

        // Configure Monolog to log on user named log files
        $filename = storage_path('logs/medicine-import-'.  posix_getpwuid(posix_geteuid())['name'] .'.log');
        $rotatingHandler = new RotatingFileHandler($filename);
        $logger->pushHandler($rotatingHandler);
        return $logger;
    }
}