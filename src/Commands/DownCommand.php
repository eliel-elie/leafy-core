<?php

namespace LeafyTech\Core\Commands;

class DownCommand extends BaseCommand
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'down';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Put the application into maintenance';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        try {
            if (is_file(ROOT_DIR . '/storage/framework/down')) {
                $this->comment('Application is already down.');

                return 0;
            }

            file_put_contents(
                ROOT_DIR . '/storage/framework/down',
                json_encode($this->getDownFilePayload(), JSON_PRETTY_PRINT)
            );

            $this->comment('Application is now in maintenance mode.');

        } catch (Exception $e) {
            $this->error('Failed to enter maintenance mode.');

            $this->error($e->getMessage());

            return 1;
        }
    }

    /**
     * Get the payload to be placed in the "down" file.
     *
     * @return array
     */
    protected function getDownFilePayload(): array
    {
        return [
            'redirect'  => null,
            'retry'     => null,
            'refresh'   => null,
            'status'    => 503,
            'template'  => null,
        ];
    }
}