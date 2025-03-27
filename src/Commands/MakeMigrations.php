<?php

namespace LeafyTech\Core\Commands;


use Illuminate\Database\Console\Migrations\TableGuesser;
use Illuminate\Database\Migrations\MigrationCreator;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Str;
use LeafyTech\Core\Application;

class MakeMigrations extends BaseCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'make:migration {name : The name of the migration}
        {--create= : The table to be created}
        {--table= : The table to migrate}
        {--path= : The location where the migration file should be created}
        {--fullpath : Output the full path of the migration}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new migration file';

    /**
     * The migration creator instance.
     *
     * @var MigrationCreator
     */
    protected $creator;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();

        $this->creator = new MigrationCreator(new Filesystem(), $this->getStubPath());
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(): int
    {
        $name = Str::snake(trim($this->input->getArgument('name')));

        $table = $this->input->getOption('table');

        $create = $this->input->getOption('create') ?: false;

        if (! $table && is_string($create)) {
            $table = $create;
            $create = true;
        }

        if (! $table) {
            [$table, $create] = TableGuesser::guess($name);
        }

        $this->writeMigration($name, $table, $create);

        return 0;
    }

    /**
     * Write the migration file to disk.
     *
     * @param string $name
     * @param string $table
     * @param bool $create
     * @return string
     * @throws \Exception
     */
    protected function writeMigration($name, $table, $create)
    {
        $file = $this->creator->create(
            $name, $this->getMigrationPath(), $table, $create
        );

        if (! $this->option('fullpath')) {
            $file = pathinfo($file, PATHINFO_FILENAME);
        }

        $this->line("<info>Created Migration:</info> {$file}");
    }

    /**
     * Get migration path (either specified by '--path' option or default location).
     *
     * @return string
     */
    protected function getMigrationPath(): string
    {
        if (! is_null($targetPath = $this->input->getOption('path'))) {
            return app()->basePath($targetPath);
        }
        return parent::getMigrationPath();
    }
}