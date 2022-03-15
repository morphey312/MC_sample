<?php

namespace App\V1\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Redis;
use OneSTransaction;

class SendOneSTransactions implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable;

    protected $data;

    /**
     * The number of times the job may be attempted.
     *
     * @var int
     */
    public $tries = 5;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($data)
    {
        /**
         * Send Job to this Queue by default
         *
         * */
        $this->queue = 'OneS';

        $this->data = $data;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        if (empty($this->data)) {
            return;
        }

        try {
            OneSTransaction::execute($this->data);
        } catch (\Throwable $e) {
            $this->fail($e);
        }

    }
}
