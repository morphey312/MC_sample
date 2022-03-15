<?php

namespace App\V1\Console\Commands;

use Illuminate\Console\Command;
use RecursiveIteratorIterator;
use RecursiveDirectoryIterator;
use Illuminate\Support\Str;
use StdClass;
use Exception;

class GrabTranslationsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'translate:grab {--modify} {--unique} {--with-baked} {--only-missing} {--path=} {--merge=} {--languages=} {outfile}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Grab translation strings from the source';

    /**
     * @var array
     */ 
    protected $entries = [];
    
    /**
     * @var array
     */ 
    protected $uniqueEntries = [];
    
    /**
     * @var bool
     */ 
    protected $modify;
    
    /**
     * @var bool
     */ 
    protected $unique;

    /**
     * @var bool
     */ 
    protected $withBaked;

    /**
     * @var bool
     */ 
    protected $onlyMissing;

    /**
     * @var array
     */
    protected $languages = [''];
    
    /**
     * @var string
     */ 
    protected $currentFile;
    
    /**
     * @var int
     */ 
    protected $currentLine;
    
    /**
     * @var bool
     */ 
    protected $touched;
    
    /**
     * @var bool
     */ 
    protected $isScript;

    /**
     * @var array
     */
    protected $excludePath;

    /**
     * @var bool
     */
    protected $ignoreTranslations = false;

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->modify = $this->option('modify');
        $this->unique = $this->option('unique');
        $this->withBaked = $this->option('with-baked');
        $this->onlyMissing = $this->option('only-missing');
        $outfile = $this->argument('outfile');
        $path = $this->option('path');
        $merge = $this->option('merge');
        $this->languages = $this->option('languages');
        if (empty($path)) {
            $path = base_path('resources/js');
        }
        $files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($path));

        $this->excludePath = [
            base_path('resources/js/lang') . '/',
            base_path('resources/js/vendors') . '/',
        ];
        
        foreach ($files as $file) {
            if ($this->shouldIgnoreFile($file->getPathname())) {
                continue;
            }
            if (!$file->isDir()) {
                $this->scanFile($file->getPathname());
            }
        }
        
        $this->dumpEntries($outfile, $merge);
    }

    /**
     * Check if file should be ignored
     * 
     * @param string $path
     * 
     * @return bool
     */
    protected function shouldIgnoreFile($path)
    {
        foreach ($this->excludePath as $excluded) {
            if (Str::startsWith($path, $excluded)) {
                return true;
            }
        }

        return false;
    }
    
    /**
     * Scan file for translations
     * 
     * @param string $path
     */ 
    protected function scanFile($path)
    {
        $f = fopen($path, 'r');
        $this->currentFile = str_replace(base_path('resources/js/'), '', $path);
        
        if (!$f) {
            throw new Exception(sprintf('Can not open file for reading: %s', $path));
        }
        
        $this->ignoreTranslations = false;
        $this->touched = false;
        $this->currentLine = 1;
        $this->isScript = strtolower(substr($path, -3)) === '.js';
        $lines = [];

        while (!feof($f)) {
            $line = trim(fgets($f), "\n\r");
            if (strpos($line, '/** @scan-translations-off */') !== false) {
                $this->ignoreTranslations = true;
            } elseif (strpos($line, '/** @scan-translations-on */') !== false) {
                $this->ignoreTranslations = false;
            }
            if ($this->ignoreTranslations) {
                continue;
            }
            if (!$this->isScript && stripos($line, '<script>') !== false) {
                $this->isScript = true;
            }
            $line = $this->scanLine($line);
            $lines[] = $line;
            $this->currentLine++;
        }
        
        fclose($f);

        if ($this->touched) {
            $content = implode("\n", $lines);
            if (!file_put_contents($path, $content)) {
                throw new Exception(sprintf('Can not modify file: %s', $path));
            }
        }
    }
    
    /**
     * Scan line for translations
     * 
     * @param string $line
     * 
     * @return string
     */ 
    protected function scanLine($line)
    {
        if (!$this->isScript) {
            $line = $this->checkAttributes($line);
            $line = $this->checkTextNodes($line);
        }
        
        $line = $this->checkStringLiterals($line);
        
        return $line;
    }
    
    /**
     * Scan html attributes for translations
     * 
     * @param string $line
     * 
     * @return string
     */ 
    protected function checkAttributes($line)
    {
        return preg_replace_callback('#([:@]?[.a-zA-Z0-9_-]+)="([^"]+)"#', function($matches) {
            $original = $matches[0];
            $attributeName = $matches[1];
            $firstChar = substr($attributeName, 0, 1);

            if ($firstChar === ':' || $firstChar === '@' || 
                $attributeName === 'v-html' || $attributeName === 'v-text') {
                // Another method is responsible for this
                return $original;
            }
            
            $text = $matches[2];
            
            if ($this->isCyr($text)) {
                $this->addEntry($text);

                if ($this->modify) {
                    $this->touched = true;
                    return sprintf(':%s="__(\'%s\')"', $attributeName, addcslashes($text, "'"));
                }
            }
            
            return $original;
        }, $line);
    }
    
    /**
     * Scan html test nodes for translations
     * 
     * @param string $line
     * 
     * @return string
     */ 
    protected function checkTextNodes($line)
    {
        if (stripos($line, ':breadcrumbs="') !== false) {
            // exception for "breadcrumbs" attribute
            return $line;
        }
        
        return preg_replace_callback('#(\>|\}\}|^)(\s*)([^\<\{]+)(\s*)(\<|\{\{|$)#', function($matches) {
            $original = $matches[0];
            $text = $matches[3];
            
            if (preg_match('#:?[a-zA-Z_-]+="#', $text)) {
                // Another method is responsible for this
                return $original;
            }
            
            $firstChar = substr($text, 0, 1);
            if ($firstChar === '`' || $firstChar === "'") {
                // Another method is responsible for this
                return $original;
            }
            
            $prefix = $matches[1] . $matches[2];
            $suffix = $matches[4] . $matches[5];

            if ($this->isCyr($text)) {
                $this->addEntry($text);

                if ($this->modify) {
                    $this->touched = true;
                    return $prefix . sprintf('{{ __(\'%s\') }}', addcslashes($text, "'")) . $suffix;
                }
            }            
            
            return $original;
        }, $line);
    }
    
    /**
     * Scan js string literals for translations
     * 
     * @param string $line
     * 
     * @return string
     */ 
    protected function checkStringLiterals($line)
    {
        $regexp = $this->isScript 
            ? '#(__[(])?([\\\'"`])([^\\\'"`]+)\2#'
            : '#(__[(])?([\\\'`])([^\\\'`]+)\2#';
            
        return preg_replace_callback($regexp, function($matches) {
            $original = $matches[0];
            $predicat = $matches[1];
            $text = $matches[3];

            if ($predicat === '__(') {
                // Already processed
                if ($this->withBaked) {
                    $this->addEntry($text);
                }
                return $original;
            }
            
            if ($this->isCyr($text)) {
                $this->addEntry($text);

                if ($this->modify) {
                    $this->touched = true;
                    return sprintf('__(\'%s\')', addcslashes($text, "'"));
                }
            }
            
            return $original;
        }, $line);
    }
    
    /**
     * Add translation entry to the list
     * 
     * @param string $entry
     */ 
    protected function addEntry($entry)
    {
        $entry = trim($entry);

        if ($this->unique) {
            if (array_key_exists($entry, $this->uniqueEntries)) {
                return;
            }
        }
        
        $obj = new StdClass();
        $obj->t = $entry;
        $obj->f = $this->currentFile;
        $obj->l = $this->currentLine;
        $this->entries[] = $obj;
        
        if ($this->unique) {
            $this->uniqueEntries[$entry] = $obj;
        }
    }
    
    /**
     * Check if string contains cyrillic letters
     * 
     * @param string $str
     * 
     * @return bool
     */ 
    protected function isCyr($str)
    {
        return preg_match('#[а-яА-Я]#', $str);
    }
    
    /**
     * Dump entries to a file
     * 
     * @param string $outfile
     * @param string $mergefile
     */ 
    protected function dumpEntries($outfile, $mergefile)
    {
        if (count($this->entries) !== 0) {
            $f = fopen($outfile, 'w');
            
            if (!$f) {
                throw new Exception(sprintf('Can not open file for writing: %s', $outfile));
            }

            $merges = [];
            $languages = explode(',', $this->languages);

            if (!empty($mergefile)) {
                $fm = fopen($mergefile, 'r');
                if (!$fm) {
                    throw new Exception(sprintf('Can not open file for reading: %s', $mergefile));
                }
                fgetcsv($fm);
                while (($data = fgetcsv($fm)) !== false) {
                    $key = trim($data[0]);
                    if (!empty($key)) {
                        $translation = [];
                        $index = 1;
                        $hasTrans = false;
                        foreach ($languages as $lang) {
                            $t = trim($data[$index++]);
                            $translation[] = $t;
                            if (!empty($t)) {
                                $hasTrans = true;
                            }
                        }
                        if ($hasTrans) {
                            $merges[$key] = $translation;
                        }
                    }
                }
                fclose($fm);
            }

            $headers = ['Оригинал'];
            foreach ($languages as $lang) {
                $headers[] = 'Перевод '.$lang;
            }
            $headers[] = 'Файл';
            $headers[] = 'Строка';
        
            fputcsv($f, $headers);
            
            foreach ($this->entries as $entry) {
                $row = [$entry->t];
                if (array_key_exists($entry->t, $merges)) {
                    if ($this->onlyMissing) {
                        continue;
                    }
                    foreach ($merges[$entry->t] as $trans) {
                        $row[] = $trans;
                    }
                } else {
                    foreach ($languages as $lang) {
                        $row[] = '';
                    }
                }
                $row[] = $entry->f;
                $row[] = $entry->l;
                fputcsv($f, $row);
            }
            
            fclose($f);
        }
    }
}