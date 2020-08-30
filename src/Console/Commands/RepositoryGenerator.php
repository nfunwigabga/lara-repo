<?php
namespace Nfunwigabga\LaraRepo\Console\Commands;

use Illuminate\Support\Str;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Symfony\Component\Console\Input\InputOption;

class RepositoryGenerator extends Command
{

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
     protected $signature = 'make:repo {model}';

     /**
     * The console command description.
     *
     * @var string
     */
     protected $description = 'Generate a repository class including the related contracts and optionally, the related model';

    /**
     * Overriding existing files.
     *
     * @var bool
     */
     protected $override = false;

    /**
     * Get all the stubs.
     *
     * @var string
     */ 
    protected $stubs = [
        'contract'   => __DIR__.'/../Stubs/Contract.stub',
        'repository' => __DIR__.'/../Stubs/Repository.stub',
        'criterion' => __DIR__.'/../Stubs/Criterion.stub',
    ];

    protected $directories = [];
    protected $namespaces = [];

    /**
     * Laravel's file manager class
     */
     protected $fileManager;

     protected $appNamespace;

     protected $baseModelName;

     protected $fullModelClass;

     /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
        $this->fileManager = app('files');
        $this->appNamespace = app()->getNamespace();
        $this->setDirectories();
        $this->setNamespaces();
        
    }

     /**
     * Execute the console command.
     *
     * @throws FileException
     * @return void
     */
    public function handle()
    {
        // Check if the model exists and provide one if it is missing.
        $this->checkModel();

        // create the contract
        list($contract, $contractName) = $this->createContract();
        
        // create the repository class
        $this->createRepository($contract, $contractName);

    }

    protected function createRepository($contract, $contractName)
    {
        $content = $this->fileManager->get($this->stubs['repository']);
        $className = "{$this->baseModelName}Repository";
        $replacements = [
            '__RepositoryNamespace__' => $this->namespaces['repositories'],
            '__ModelNamespace__' => $this->fullModelClass,
            '__InterfaceNamespace__' => $contract,
            '__RepositoryClassName__' => $className,
            '__InterfaceClassName__' => $contractName,
            '__ModelName__' => $this->baseModelName
        ];

        $content = str_replace(array_keys($replacements), array_values($replacements), $content);
        $fileName = $className;
        $fileDirectory = app()->basePath().DIRECTORY_SEPARATOR.str_replace('\\', DIRECTORY_SEPARATOR, $this->namespaces['repositories']);
        
        // Check if the directory exists, if not create...
        if (!$this->fileManager->exists($fileDirectory)) {
            $this->fileManager->makeDirectory($fileDirectory, 0755, true);
        }
        
        $filePath = $fileDirectory.'/'.$fileName.'.php';
        
        if ($this->laravel->runningInConsole() && $this->fileManager->exists($filePath)) {
            if (! $this->confirm("The repository [{$fileName}] already exists. Do you want to overwrite it?")) {
                $this->line("The repository [{$fileName}] will not be overwritten.");
                return;
            }
        }

        $this->fileManager->put($filePath, $content);

        $this->line("The repository [{$fileName}] has been created.");
    }

    protected function checkModel()
    {
        $model = $this->namespaces['models'] . '\\' . $this->argument('model');
        $this->fullModelClass = str_replace('/', DIRECTORY_SEPARATOR, $model);
        
        if(! class_exists($this->fullModelClass)){
            $qn = "Model [{$this->fullModelClass}] does not exist. Would you like to create it now?";
            if ($this->confirm($qn)) {
                $migrate = "Would you also like to generate a migration for this migration?";
                if($this->confirm($migrate)){
                    Artisan::call('make:model', [
                        'name' => $this->fullModelClass,
                        '--migration' => true
                    ]);
                } else {
                    Artisan::call('make:model', [
                        'name' => $this->fullModelClass
                    ]);
                }
                $this->line("Model [{$this->fullModelClass}] has been successfully created."); 
            } else {
                $this->line("Model [{$this->fullModelClass}] is missing. Make sure you create one.");
            }
        }

        $this->baseModelName = $this->argument('model');
    }

    protected function createContract()
    {
        $content = $this->fileManager->get($this->stubs['contract']);
        $className = "I{$this->baseModelName}";

        $replacements = [
            '__ContractNamespace__' => $this->namespaces['contracts'],
            '__InterfaceName__' => $className,
        ];
        $content = str_replace(array_keys($replacements), array_values($replacements), $content);
        $fileName = $className;
        $fileDirectory  = app()->basePath(). DIRECTORY_SEPARATOR .str_replace('\\', DIRECTORY_SEPARATOR, $this->namespaces['contracts']);

        if (!$this->fileManager->exists($fileDirectory)) {
            $this->fileManager->makeDirectory($fileDirectory, 0755, true);
        }
        $filePath = $fileDirectory. DIRECTORY_SEPARATOR .$fileName.'.php';

        if ($this->laravel->runningInConsole() && $this->fileManager->exists($filePath)) {
            if (! $this->confirm("The contract [{$fileName}] already exists. Do you want to overwrite it?")) {
                $this->line("The contract [{$fileName}] will not be overwritten.");
                return;
            }
        }
        $this->fileManager->put($filePath, $content);
        $this->line("The contract [{$fileName}] has been created.");

        return [$this->namespaces['contracts'].'\\'.$fileName, $fileName];

    }

    protected function setDirectories()
    {
        $this->directories['models'] = config('laravel-repo.models_directory');
        $this->directories['repositories'] = config('laravel-repo.repositories_directory');
        $this->directories['contracts'] = config('laravel-repo.contracts_directory');
        $this->directories['criteria'] = config('laravel-repo.criteria_directory');
    }

    protected function setNamespaces()
    {
        $this->namespaces['models'] = $this->appNamespace . $this->directories['models'];
        $this->namespaces['repositories'] = $this->appNamespace . $this->directories['repositories'];
        $this->namespaces['contracts'] = $this->appNamespace . $this->directories['repositories'] . '\\' . $this->directories['contracts'];
        $this->namespaces['criteria'] = $this->appNamespace . $this->directories['repositories'] . '\\' . $this->directories['criteria'];
    }

    



}