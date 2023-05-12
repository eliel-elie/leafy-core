<?php

namespace LeafyTech\Core\Commands;

use Illuminate\Container\Container;
use Illuminate\Database\ConnectionResolverInterface;
use Illuminate\Database\Migrations\DatabaseMigrationRepository;
use Illuminate\Database\Migrations\MigrationRepositoryInterface;
use Symfony\Component\Console\Input\InputOption;

class InstallCommand extends BaseCommand
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'migrate:install';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create the migration repository';

    /**
     * The repository instance.
     *
     * @var MigrationRepositoryInterface
     */
    protected $repository;

    protected $capsule;

    /**
     * Create a new migration install command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();

        global $db;

        $this->capsule = $db->getCapsule();

        $container = Container::getInstance();
        $resolve   = $container->instance(ConnectionResolverInterface::class, $this->capsule->getDatabaseManager());

        $this->repository = new DatabaseMigrationRepository($resolve, 'migrations');

    }

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        $this->repository->setSource($this->input->getOption('database'));

        $this->repository->createRepository();

        $this->info('Migration table created successfully.');
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
        ];
    }
}