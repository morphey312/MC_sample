<?php

namespace App\V1\Jobs;


use Illuminate\Bus\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Redis;
use OneSMedicineIssue;
use App\V1\Models\Appointment\Service;

class SendOneSMedicineIssue implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable;

    /**
     * Medicines list array
     */
    protected $service;

    /**
     * Logged employee user
     */
    protected $user;

    /**
     * Issued medicine document
     */
    protected $document;

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
    public function __construct(Service $service, $user = null, $document = null)
    {
        /**
         * Send Job to this Queue by default
         *
         * */
        $this->queue = 'OneS';

        $this->service = $service;
        $this->user = $user;
        $this->document = $document;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        if ($this->service == null) {
            return;
        }

        OneSMedicineIssue::execute($this->service, $this->user, $this->document);
    }
}
