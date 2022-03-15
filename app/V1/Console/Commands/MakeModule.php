<?php

namespace App\V1\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\View\View;
use Illuminate\View\FileViewFinder;

class MakeModule extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'make:module {--all : Include all features} {--app-version=V1 : The application version} {name : The name of the module (please use CamelCase)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create all necessary files for a module';
    
    /**
     * Features
     * 
     * @var array
     */ 
    protected $features = [
        'model' => [
            'app/{version}/Models/{ModuleName}.php' => 'templates::model',
        ],
        'controller' => [
            'app/{version}/Http/Controllers/{ModuleName}Controller.php' => 'templates::controller',
        ],
        'request' => [
            'app/{version}/Http/Requests/{ModuleName}Request.php' => 'templates::request',
        ],
        'policy' => [
            'app/{version}/Policies/{ModuleName}Policy.php' => 'templates::policy',
        ],
        'repository' => [
            'app/{version}/Repositories/{ModuleName}Repository.php' => 'templates::repository',
            'app/{version}/Contracts/Repositories/{ModuleName}Repository.php' => 'templates::repository-interface',
        ],
        'resource' => [
            'app/{version}/Http/Resources/{ModuleName}Resource.php' => 'templates::resource',
        ],
        'query' => [
            'app/{version}/Repositories/Query/{ModuleName}Filter.php' => 'templates::filter',
            'app/{version}/Contracts/Repositories/Query/{ModuleName}Filter.php' => 'templates::filter-interface',
            'app/{version}/Repositories/Query/{ModuleName}Sorter.php' => 'templates::sorter',
            'app/{version}/Contracts/Repositories/Query/{ModuleName}Sorter.php' => 'templates::sorter-interface',
        ],
        'service-provider' => [
            'app/{version}/Providers/{ModuleName}ServiceProvider.php' => 'templates::service-provider',
        ],
    ];

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        app()['view']->prependNamespace('templates', [__DIR__ . '/templates']);

        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $moduleName = $this->argument('name');
        $allFeatures = $this->option('all');
        $version = $this->option('app-version');
        
        $components = explode('\\', $moduleName);
        $camelCase = implode('/', $components);
        $kebabCase = implode('/', array_map('kebab_case', $components));
        
        $baseName = array_pop($components);
        $namespace = count($components) ? implode('\\', $components) : null;
        
        $affectedFiles = [];
        
        foreach ($this->features as $feature => $files) {
            if ($allFeatures || $this->confirm(sprintf('Do you wish to include feature `%s`?', $feature))) {
                foreach ($files as $destination => $template) {
                    $fileName = base_path(strtr($destination, [
                        '{version}' => $version,
                        '{ModuleName}' => $camelCase,
                        '{module-name}' => $kebabCase,
                    ]));
                    
                    if (is_file($fileName) && !$this->confirm(sprintf('File `%s` already exists! Overwrite it?', $fileName))) {
                        if (!$this->confirm('Do you want to generate the remaining files?')) {
                            return $this->abort();
                        }
                        continue;
                    }
                    
                    $this->info(sprintf('Writing file `%s` using template `%s`', $fileName, $template));
                    $content = view($template, [
                        'fullName' => $moduleName, 
                        'name' => $baseName, 
                        'namespace' => $namespace,
                        'version' => $version,
                    ])->render();
                    
                    $directoryName = dirname($fileName);
                    if (!is_dir($directoryName) && false === @mkdir($directoryName, 0755, true)) {
                        $this->error(sprintf('Error while making directory `%s`!', $directoryName));
                        $this->abort(1);
                    }
                    
                    if (false === @file_put_contents($fileName, $content)) {
                        $this->error(sprintf('Error while writing file `%s`!', $fileName));
                        $this->abort(1);
                    }
                    
                    $affectedFiles[] = $fileName;
                }
            }
        }
        
        $this->line('');
        $this->info('Job done.');
        $this->info('The affected files are listed below:');
        $this->line('');
        
        foreach ($affectedFiles as $file) {
            $this->info($file);
        }
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
}
