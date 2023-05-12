<?php

namespace LeafyTech\Core\Commands;

use Illuminate\Container\Container;
use Illuminate\Database\ConnectionResolverInterface;
use Illuminate\Database\Migrations\DatabaseMigrationRepository;
use Illuminate\Database\Migrations\Migrator;
use Illuminate\Events\Dispatcher;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Arr;

class RollbackCommand extends BaseCommand
{
    /**
     * The name and signature of the console command.
     * @var string
     */
    protected $signature = 'migrate:rollback';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Rollback the last database migration';

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
        $migrations = $this->migrator->getRepository()->getLast();
        $files      = $this->migrator->getMigrationFiles($this->getMigrationPath());

        if(count($migrations) == 0) {
            $this->info('<info>Nothing to rollback.</info>');
            return 0;
        }

        $rolledBack     = [];

        $this->requireFiles($files);

        foreach ($migrations as $migration) {
            $migration = (object)$migration;

            if (! $file = Arr::get($files, $migration->migration)) {
                $this->comment("<fg=red>Migration not found:</> {$migration->migration}");
                continue;
            }

            $rolledBack[] = $file;

            $name  = $this->migrator->getMigrationName($file);
            $class = $this->migrator->resolve($name);

            $this->comment("Rolling back: {$name}");

            $startTime = microtime(true);

            $class->Down();

            $this->migrator->getRepository()->delete($migration);

            $runTime = number_format((microtime(true) - $startTime) * 1000, 2);

            $this->info("Rolled back:  {$name} ({$runTime}ms)");
        }

        return $rolledBack;
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


}