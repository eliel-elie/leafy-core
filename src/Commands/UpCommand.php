<?php

namespace LeafyTech\Core\Commands;

class UpCommand extends BaseCommand
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'up';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Bring the application out of maintenance mode';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        try {
            if (! is_file(ROOT_DIR . '/storage/framework/down')) {
                $this->comment('Application is already up.');

                return 0;
            }

            unlink(ROOT_DIR . '/storage/framework/down');

            $this->info('Application is now live.');

        } catch (Exception $e) {
            $this->error('Failed to disable maintenance mode.');

            $this->error($e->getMessage());

            return 1;
        }
    }
}