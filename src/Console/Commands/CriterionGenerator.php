<?php
namespace Nfunwigabga\LaraRepo\Console\Commands;

use Illuminate\Support\Str;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Symfony\Component\Console\Input\InputOption;

class CriterionGenerator extends Command
{

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
     protected $signature = 'make:criteria {criteria_name}';

     /**
     * The console command description.
     *
     * @var string
     */
     protected $description = 'Generate a criterion for a repository';

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
    protected $stub = __DIR__.'/../Stubs/Criterion.stub';
    
    protected $namespace;

    /**
     * Laravel's file manager class
     */
     protected $fileManager;

     protected $appNamespace;

     protected $criteriaBaseName;

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
        $directory = config('laravel-repo.repositories_directory') . '\\' . config('laravel-repo.criteria_directory');
        $this->criteriaBaseName = $this->argument('criteria_name');
        $this->namespace = $this->appNamespace . $directory;
         
        // create the criterion class
        $this->createCriterion();

    }


    protected function createCriterion()
    {
        $content = $this->fileManager->get($this->stub);
        $className = $this->criteriaBaseName;

        $replacements = [
            '__CriteriaNamespace__' => $this->namespace,
            '__CriterionName__' => $className
        ];
        $content = str_replace(array_keys($replacements), array_values($replacements), $content);
        $fileName = $className;
        $fileDirectory  = app()->basePath(). DIRECTORY_SEPARATOR .str_replace('\\', DIRECTORY_SEPARATOR, $this->namespace);

        if (!$this->fileManager->exists($fileDirectory)) {
            $this->fileManager->makeDirectory($fileDirectory, 0755, true);
        }
        $filePath = $fileDirectory.'/'.$fileName.'.php';

        if ($this->laravel->runningInConsole() && $this->fileManager->exists($filePath)) {
            if (! $this->confirm("The criterion [{$fileName}] already exists. Do you want to overwrite it?")) {
                $this->line("The criterion [{$fileName}] will not be overwritten.");
                return;
            }
        }
        $this->fileManager->put($filePath, $content);
        $this->line("The criterion [{$fileName}] has been created.");

    }




}