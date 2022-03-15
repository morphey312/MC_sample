<?php

namespace App\V1\Console\Commands\OneS;

use App\V1\Services\OneS\ImportClientService;
use Illuminate\Support\Facades\Log;

trait Import
{
    /**
     * @var GuzzleHttp\Client
     */
    protected $client;

    /**
     * @var string log channel
     */
    protected $logChannel = 'medicineImportLog';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
        $this->client = new ImportClientService();
    }

    /**
     * Get stores
     *
     * @return array
     */
    protected function getData()
    {
        return $this->client->sendImportCommand(self::COMMAND);
    }

    /**
     * Store recieved results
     *
     * @param array $results
     */
    protected function saveResults($results)
    {
        $bar = $this->output->createProgressBar(count($results));
        $bar->start();

        foreach ($this->getRow($results) as $row) {
            if ($this->save($row)) {
                $bar->advance();
            } else {
                $bar->finish();
                $this->write('There is an error on saving "' . ($row['description'] ?? '') . $this->getGuidKeyValue($row), true);
                $this->abort(1);
            }
        }
         $bar->finish();
    }

    /**
     * Get importing item uid string
     *
     * @param array $row
     *
     * @return string
     */
    protected function getGuidKeyValue($row)
    {
        if (isset($row['id'])) {
            return ' id ' . $row['id'];
        }

        if (isset($row['item'])) {
            return ' item ' . $row['item'];
        }

        $key = array_keys($row)[0];
        return $key . " " . $row[$key];
    }
    /**
     * Get single store row
     *
     * @param array $results
     */
    protected function getRow($results, $assoc = false)
    {
        foreach ($results as $id => $row) {
            if ($assoc) {
                yield $id => $row;
            } else {
                yield $row;
            }
        }
    }

    /**
     * Terminate the process
     *
     * @param int $exitCode
     */
    protected function abort($exitCode = 0)
    {
        $this->write('Aborting...');
        exit($exitCode);
    }

    /**
     *  Write to log file
     *
     * @param string $message
     * @param bool $isError
     */
    protected function write($message, $isError = false)
    {
        if ($isError) {
            Log::channel($this->logChannel)->error($message);
        } else {
            Log::channel($this->logChannel)->info($message);
        }
    }
}
