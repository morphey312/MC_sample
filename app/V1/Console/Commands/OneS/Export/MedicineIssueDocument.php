<?php

namespace App\V1\Console\Commands\OneS\Export;

use Illuminate\Console\Command;
use App\V1\Jobs\SendOneSMedicineIssue;
use App\V1\Models\Patient\IssuedMedicine\Document;
use App\V1\Models\User;

class MedicineIssueDocument extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'export:issued_document {document_ids*}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Export medicine issue documents';

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
        $documents = $this->argument('document_ids');

        if (empty($documents)) {
            $this->info("Input payments ids");
            return;
        }

        foreach ($documents as $doc_id) {
            $document = Document::with('appointment_service')->where('id', '=', $doc_id)->first();

            if ($document->appointment_service_id === null) {
                echo "Document №" . $doc_id . " doesnt have related appointment service";
                continue;
            }

            $user = User::find($document->created_by_id);

            if ($user && $employee = $user->getEmployeeModel()) {
                SendOneSMedicineIssue::dispatch($document->appointment_service, $employee, $document);
                continue;
            }

            echo "Document №" . $doc_id . ", verify issuer exists";
            continue;
        }
    }
}
