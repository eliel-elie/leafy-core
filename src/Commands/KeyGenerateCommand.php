<?php

namespace LeafyTech\Core\Commands;

class KeyGenerateCommand extends BaseCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'key:generate
                    {--show : Display the key instead of modifying files}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Set the application key';

    /**
     * Execute the console command.
     *
     * @return void
     * @throws \Exception
     */
    public function handle()
    {
        $key = 'base64:'.base64_encode(random_bytes( 32));

        if ($this->option('show')) {
            return $this->comment($key);
        }

        $this->writeNewEnvironmentFileWith($key);

        $this->info('Application key set successfully.');
    }

    /**
     * Write a new environment file with the given key.
     *
     * @param  string  $key
     * @return void
     */
    protected function writeNewEnvironmentFileWith($key)
    {
        file_put_contents(ROOT_DIR . '/.env', preg_replace(
            $this->keyReplacementPattern(),
            'APP_KEY='.$key,
            file_get_contents(ROOT_DIR . '/.env')
        ));
    }

    /**
     * Get a regex pattern that will match env APP_KEY with any random key.
     *
     * @return string
     */
    protected function keyReplacementPattern(): string
    {
        $escaped = preg_quote('='.$_ENV['APP_KEY'], '/');

        return "/^APP_KEY{$escaped}/m";
    }
}