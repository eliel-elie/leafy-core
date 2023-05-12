<?php

namespace LeafyTech\Core\Commands;

use PHPUnit\TextUI\Command;

class TestCommand extends BaseCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test
                            {--list : List available tests}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Run the application tests';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $options = 'tests';

        if ($this->option('list')) {
            $options = '--list-tests';
        }

        $command = new Command();
        $command->run(['phpunit', $options]);

    }

}