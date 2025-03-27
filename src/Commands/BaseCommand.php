<?php

namespace LeafyTech\Core\Commands;

use Illuminate\Console\Command;
use LeafyTech\Core\Application;

class BaseCommand extends Command
{
    /**
     * Get the path to the migration directory.
     *
     * @return string
     */
    protected function getMigrationPath(): string
    {
        return ROOT_DIR . '/Migrations';
    }

    /**
     * Get the path to the stub directory.
     *
     * @return string
     */
    protected function getStubPath(): string
    {
        return __DIR__ . '/Stubs';
    }

}