<?php

namespace App\V1\Console\Commands;

use Illuminate\Console\Command;
use Exception;

class DumpTranslationsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'translate:dump {outfile} {infile*}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Dump translations file';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $infile = $this->argument('infile');
        $outfile = $this->argument('outfile');
        $count = count($infile);
       
        $data = [];

        foreach ($infile as $fileindex => $file) {
            $jsonData = file_get_contents($file);
            if (!$jsonData) {
                throw new Exception(sprintf('Can not open file for reading: %s', $file));
            }
            $jsonData = str_replace('/** @scan-translations-off */ export default {', '{', $jsonData);
            $jsonData = trim($jsonData);
            $jsonData = rtrim($jsonData, ';');
            $json = json_decode($jsonData, true);
            if (!is_array($json)) {
                throw new Exception(sprintf('Invalid JSON in file %s', $file));
            }
            foreach ($json as $key => $value) {
                if (!isset($data[$key])) {
                    $data[$key] = array_fill(0, $count, '');
                    $data[$key][0] = $key;
                }
                $data[$key][$fileindex + 1] = $value;
            }
        }
        
        $f = fopen($outfile, 'w');
            
        if (!$f) {
            throw new Exception(sprintf('Can not open file for writing: %s', $outfile));
        }

        $headers = ['Оригинал'];
        foreach ($infile as $file) {
            $headers[] = basename($file, '.js');
        }
        
        fputcsv($f, $headers);
            
        foreach ($data as $row) {
            fputcsv($f, $row);
        }
        
        fclose($f);
    }
}