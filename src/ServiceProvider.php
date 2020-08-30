<?php
namespace Nfunwigabga\LaraRepo;

class ServiceProvider extends \Illuminate\Support\ServiceProvider 
{

    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = false;


    /**
     * Bootstrap the application events.
     *
     * @return void
     */
    public function boot()
    {
        $this->publishConfig();
        $this->registerBindings();
        
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfig();
        $this->registerCommands();
    }

    /**
     * Publish configuration.
     */
    protected function publishConfig()
    {
        $configPath = __DIR__.'/config/laravel-repo.php';
 
        if (function_exists('config_path')) {
            $publishPath = config_path('laravel-repo.php');
        } else {
            $publishPath = base_path('config/laravel-repo.php');
        }
 
        // Publish config files
        $this->publishes([$configPath => $publishPath], 'repository-config');
    }

    /**
     * Merges configs.
     *
     * @return void
     */
    protected function mergeConfig()
    {
        $this->mergeConfigFrom(__DIR__.'/config/laravel-repo.php', 'laravel-repo');
    }
 
     /**
      * Register package commands.
      */
    protected function registerCommands()
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                Console\Commands\RepositoryGenerator::class,
                Console\Commands\CriterionGenerator::class,
            ]);
        }
    }


    protected function registerBindings()
    {
        // automatically bind contracts and repositories
        $repo_directory = config('laravel-repo.repositories_directory');
        $repoNamespace = app()->getNamespace() . $repo_directory;
        $repo_path = app()->basePath().DIRECTORY_SEPARATOR.str_replace('\\', DIRECTORY_SEPARATOR, $repoNamespace);

        if (app('files')->exists($repo_path)) {
            $rdi = new \RecursiveDirectoryIterator(app_path($repo_directory));
            $it = new \RecursiveIteratorIterator($rdi);
            $contracts = [];
            $repositories = [];
            while ($it->valid()) {
                if (! $it->isDot() && $it->isFile() && $it->isReadable() && $it->current()->getExtension() === 'php') {
                    $filename = substr($it->getFilename(), 0, -4);
                    if (strpos($filename, "Repository") !== false) {
                        array_push($repositories, $filename);
                    } else {
                        array_push($contracts, $filename);
                    }
                }
                $it->next();
            }

            $contractNamespace = $repoNamespace . '\\' . config('laravel-repo.contracts_directory');
            $bindings = [];
            foreach($contracts as $contract){
                $contractModelName = ltrim($contract, $contract[0]);
                $contractFullPath = $contractNamespace . '\\' . $contract;
                
                foreach($repositories as $repo){
                    $repoModelName = str_replace('Repository', '', $repo);
                    if($repoModelName == $contractModelName){
                        $repoFullPath = $repoNamespace . '\\' . $repo;
                        array_push($bindings, [$contractFullPath, $repoFullPath]);
                    }
                }
            }

            foreach($bindings as $binding){
                $this->app->bind(
                    $binding[0],
                    $binding[1]
                );
            }
        }
    }



}