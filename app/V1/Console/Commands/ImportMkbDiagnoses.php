<?php

namespace App\V1\Console\Commands;

use Illuminate\Console\Command;
use App\V1\Models\Diagnosis;
use Illuminate\Support\Facades\DB;

class ImportMkbDiagnoses extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'import:mkb {--update-only} {path}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Seeds diagnosis table from specified csv file';

    /**
     * @var array
     */
    protected $notFound = [];

    /**
     * @var int
     */
    protected $newlyCreated = 0;

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $path = $this->argument('path');
        $updateOnly = $this->option('update-only');

        if (file_exists($path)) {
            if (is_readable($path)) {
                if (!$updateOnly) {
                    if ($this->confirm('Do you want to delete all stored Diagnosis')) {
                        $this->dropTable();
                    }
                }

                $counter = 0;
                $totalLines = $this->get_total_lines($path);
                $bar = $this->output->createProgressBar($totalLines);
                $bar->start();
                
                foreach ($this->getLine($path) as $line) {
                    $counter++;
                    if ($this->create($line, $updateOnly)) {
                        $bar->advance();
                    } else {
                        $bar->finish();
                        $this->line('');
                        $this->error('There is an error on line "' . $counter . '", code - ' . $line[0]);
                        $this->abort(1);
                    }
                }

                $bar->finish();
                $this->line('');
                $this->info('Imported ' . $counter . ' diagnosis');
                if ($updateOnly && count($this->notFound) !== 0) {
                    $this->line('');
                    $this->info('The following diagnoses were not found: ' . implode(', ', $this->notFound));
                } else if ($this->newlyCreated !== 0) {
                    $this->line('');
                    $this->info($this->newlyCreated . ' new diagnoses were created');
                }
            } else {
                $this->error('File "' . $path . '" is not readable');
                $this->abort();
            }
        } else {
            $this->error('File "' . $path . '" not found');
            $this->abort();
        }
    }

    /**
     * Delete all dianosis
     */ 
    protected function dropTable()
    {
        $tableName = with(new Diagnosis)->getTable();
        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        DB::table($tableName)->delete();
        DB::statement('SET FOREIGN_KEY_CHECKS=1');
    }

    /**
     * Terminate the process
     * 
     * @param int $exitCode
     */ 
    protected function abort($exitCode = 0)
    {
        $this->info('Aborting...');
        exit($exitCode);
    }

    /**
     * Get total file lines
     * 
     * @param string $path
     * 
     * @return int
     */ 
    protected function get_total_lines($path)
    {
        return intval(shell_exec(sprintf('wc -l < %s', $path)));
    }

    /**
     * Get file single line
     * 
     * @param string $path
     */ 
    protected function getLine($path) 
    {
        $handle = fopen($path, "r");

        while (!feof($handle)) {
            $line = fgetcsv($handle);
            yield $line;
        }

        fclose($handle);
    }

    /**
     * Create new entity
     * 
     * @param array $data
     * @param bool $updateOnly
     * 
     * @return bool
     */ 
    protected function create($data, $updateOnly)
    {
        if (trim($data[0]) === '') {
            return true;
        }

        // (UKR-ENG format)
        /* $code = trim($data[10]);
        if ($code !== '_') {
            $description = trim($data[12]);
            $descriptionEn = trim($data[11]);
            $searchCodes = [
                $code,
            ];
        } else {
            $code = trim($data[7]);
            if ($code !== '_') {
                $description = trim($data[9]);
                $descriptionEn = trim($data[8]);
                $searchCodes = [
                    $code, 
                    "{$code}+",
                    "{$code}*",
                ];
            } else {
                $code = trim($data[4]);
                if ($code !== '_') {
                    $description = trim($data[6]);
                    $descriptionEn = trim($data[5]);
                    $searchCodes = [
                        $code, 
                        "{$code}+",
                        "{$code}*",
                    ];
                } else {
                    return true;
                }
            }
        }

        $model = Diagnosis::whereIn('code', $searchCodes)->first();

        if ($model === null) {
            if ($updateOnly) {
                $this->notFound[] = $code;
                return true;
            } else {
                $this->newlyCreated++;
                $model = new Diagnosis();
                $model->code = $code;
                $model->description = $description;
                $model->description_lc2 = $descriptionEn;
                return $model->save();
            }
        } else {
            $model->code = $code;
            $model->description = $description;
            $model->description_lc2 = $descriptionEn;
            return $model->save();
        } */
        
        // Slovak format
        $code = rtrim(trim($data[0]), '.-!+*');
        $description = trim($data[1]);
        $searchCodes = (strpos($code, '-') === false) ? [
            $code, 
            "{$code}+",
            "{$code}*",
        ] : [
            $code, 
        ];

        $model = Diagnosis::whereIn('code', $searchCodes)->first();

        if ($model === null) {
            if ($updateOnly) {
                $this->notFound[] = $code;
                return true;
            } else {
                $this->newlyCreated++;
                $model = new Diagnosis();
                $model->code = $code;
                $model->description = $description;
                $model->description_lc3 = $description;
                return $model->save();
            }
        } else {
            $model->description_lc3 = $description;
            return $model->save();
        }
    }
}
