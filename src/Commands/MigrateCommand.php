<?php

namespace LeafyTech\Core\Commands;

use Illuminate\Console\ConfirmableTrait;
use Illuminate\Container\Container;
use Illuminate\Database\ConnectionResolverInterface;
use Illuminate\Database\Migrations\DatabaseMigrationRepository;
use Illuminate\Database\Migrations\Migrator;
use Illuminate\Events\Dispatcher;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Collection;

class MigrateCommand extends BaseCommand
{
    use ConfirmableTrait;

    /**
     * The name and signature of the console command.
     * @var string
     */
    protected $signature = 'migrate:run {--database= : The database connection to use}
                {--force : Force the operation to run when in production}
                {--path=* : The path(s) to the migrations files to be executed}
                {--schema-path= : The path to a schema dump file}                
                {--step : Force the migrations to be run so they can be rolled back individually}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Run the database migrations';

    /**
     * The migrator instance.
     *
     * @var Migrator
     */
    protected $migrator;

    protected $capsule;

    protected $files;

    /**
     * The event dispatcher instance.
     *
     * @var Dispatcher
     */
    protected $dispatcher;

    /**
     * Create a new migration command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();

        global $db;

        $this->capsule = $db->getCapsule();

        $container = Container::getInstance();
        $resolve = $container->instance(ConnectionResolverInterface::class, $this->capsule->getDatabaseManager());

        $this->migrator = new Migrator(
            new DatabaseMigrationRepository($this->capsule->getDatabaseManager(), 'migrations'),
            $resolve, new Filesystem()
        );

        $this->files = new Filesystem();

        $this->dispatcher = new Dispatcher($container);

    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $ranMigration   = $this->migrator->getRepository()->getRan();
        $filesMigration = $this->migrator->getMigrationFiles($this->getMigrationPath());

        $pending        = $this->pendingMigrations($filesMigration, $ranMigration);

        if(count($pending) == 0) {
            $this->info('<info>Nothing to migrate.</info>');
            return 0;
        }

        $batch = $this->migrator->getRepository()->getNextBatchNumber();

        $this->requireFiles($pending);

        foreach ($pending as $file) {

            $name  = $this->migrator->getMigrationName($file);
            $class = $this->migrator->resolve($name);

            $this->comment("Migrating: {$name}");

            $startTime = microtime(true);

            $class->Up();

            $this->migrator->getRepository()->log($name, $batch);

            $runTime = number_format((microtime(true) - $startTime) * 1000, 2);

            $this->info("Migrated:  {$name} ({$runTime}ms)");

            if ($this->option('step')) {
                $batch++;
            }
        }


        return 0;
    }

    /**
     * Get the migration files that have not yet run.
     *
     * @param  array  $files
     * @param  array  $ran
     * @return array
     */
    protected function pendingMigrations($files, $ran)
    {
        return Collection::make($files)
            ->reject(function ($file) use ($ran) {
                return in_array($this->migrator->getMigrationName($file), $ran);
            })->values()->all();
    }

    /**
     * Require in all the migration files in a given path.
     *
     * @param  array  $files
     * @return void
     */
    public function requireFiles(array $files)
    {
        foreach ($files as $file) {
            $this->files->requireOnce($file);
        }
    }

    /**
     * Prepare the migration database for running.
     *
     * @return void
     */
    protected function prepareDatabase()
    {
        //if (! $this->migrator->repositoryExists()) {
        //    $this->call('migrate:install', array_filter([
        //        '--database' => $this->option('database'),
        //    ]));
        //}
    }

}