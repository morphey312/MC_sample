<?php

namespace App\V1\Console\Commands;

use Illuminate\Console\Command;
use Exception;

class BuildTranslationsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'translate:build {--allow-empty} {--col=2} {--merge=} {infile} {outfile}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Build translations file';

    /**
     * @var bool
     */
    protected $allowEmpty;

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->allowEmpty = $this->option('allow-empty');
        $infile = $this->argument('infile');
        $outfile = $this->argument('outfile');
        $col = $this->option('col');
        $merge = $this->option('merge');
        $json = [];

        if ($merge) {
            $jsonData = file_get_contents($merge);
            if (!$jsonData) {
                throw new Exception(sprintf('Can not open file for reading: %s', $merge));
            }
            $jsonData = str_replace('/** @scan-translations-off */ export default {', '{', $jsonData);
            $jsonData = trim($jsonData);
            $jsonData = rtrim($jsonData, ';');
            $json = json_decode($jsonData, true);
            if (!is_array($json)) {
                throw new Exception(sprintf('Invalid JSON in file %s', $merge));
            }
        }

        $f = fopen($infile, 'r');
        if (!$f) {
            throw new Exception(sprintf('Can not open file for reading: %s', $infile));
        }
        fgetcsv($f);
        while (($data = fgetcsv($f)) !== false) {
            $key = trim($data[0]);
            if (empty($json[$key])) {
                $translation = trim($data[$col - 1]);
                if (!empty($key) && (!empty($translation) || $this->allowEmpty)) {
                    $json[$key] = $translation;
                }
            }
        }
        fclose($f);

        $content = '/** @scan-translations-off */ export default ' . json_encode($json, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        if (!file_put_contents($outfile, $content)) {
            throw new Exception(sprintf('Can not save file: %s', $outfile));
        }
    }
}