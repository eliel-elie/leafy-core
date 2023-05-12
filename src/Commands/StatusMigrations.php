<?php

namespace LeafyTech\Core\Commands;

use Illuminate\Container\Container;
use Illuminate\Database\ConnectionResolverInterface;
use Illuminate\Database\Migrations\DatabaseMigrationRepository;
use Illuminate\Database\Migrations\Migrator;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Collection;
use Symfony\Component\Console\Input\InputOption;

class StatusMigrations extends BaseCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'migrate:status';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Show the status of each migration';

    /**
     * The migrator instance.
     *
     * @var Migrator
     */
    protected $migrator;

    protected $capsule;

    /**
     * Create a new command instance.
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
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {

        return $this->migrator->usingConnection($this->capsule->getConnection()->getName(), function () {

            $ran = $this->migrator->getRepository()->getRan();

            $batches = $this->migrator->getRepository()->getMigrationBatches();

            if (count($migrations = $this->getStatusFor($ran, $batches)) > 0) {
                $this->table(['Ran?', 'Migration', 'Batch'], $migrations);
            } else {
                $this->error('No migrations found');
            }
        });
    }

    /**
     * Get the status for the given ran migrations.
     *
     * @param  array  $ran
     * @param  array  $batches
     * @return Collection
     */
    protected function getStatusFor(array $ran, array $batches)
    {
        return Collection::make($this->getAllMigrationFiles())
            ->map(function ($migration) use ($ran, $batches) {
                $migrationName = $this->migrator->getMigrationName($migration);

                return in_array($migrationName, $ran)
                    ? ['<info>Yes</info>', $migrationName, $batches[$migrationName]]
                    : ['<fg=red>No</fg=red>', $migrationName];
            });
    }

    /**
     * Get an array of all of the migration files.
     *
     * @return array
     */
    protected function getAllMigrationFiles(): array
    {
        return $this->migrator->getMigrationFiles($this->getMigrationPath());
    }

    /**
     * Get the console command options.
     *
     * @return array
     */
    protected function getOptions()
    {
        return [
            ['database', null, InputOption::VALUE_OPTIONAL, 'The database connection to use'],

            ['path', null, InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY, 'The path(s) to the migrations files to use'],

            ['realpath', null, InputOption::VALUE_NONE, 'Indicate any provided migration file paths are pre-resolved absolute paths'],
        ];
    }
}