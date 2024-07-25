<?php

namespace LeafyTech\Core;

use Illuminate\Container\Container;
use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Events\Dispatcher;
use Yajra\Oci8\Connectors\OracleConnector;
use Yajra\Oci8\Oci8Connection;

class Database
{
    const ORACLE_DRIVER          = 'oracle';

    protected array $sessionVars = [
        'NLS_TIME_FORMAT'         => 'HH24:MI:SS',
        'NLS_DATE_FORMAT'         => 'MM/DD/YYYY',
        'NLS_TIMESTAMP_FORMAT'    => 'MM/DD/YYYY HH24:MI:SS',
        'NLS_TIMESTAMP_TZ_FORMAT' => 'MM/DD/YYYY HH24:MI:SS TZH:TZM',
        'NLS_NUMERIC_CHARACTERS'  => '.,',
    ];

    protected Capsule $capsule;

    public function __construct($config)
    {
        $this->capsule = new Capsule;

        foreach ($config->connections as $key => $connection) {

            if ($connection['driver'] === self::ORACLE_DRIVER) {

                $manager = $this->capsule->getDatabaseManager();

                $manager->extend(self::ORACLE_DRIVER, function($configuration) use ($connection) {

                    $connector  = new OracleConnector();
                    $connection = $connector->connect($configuration);
                    $db = new Oci8Connection($connection, $connection['database'], $connection["prefix"]);

                    if (isset($configuration['schema'])) {
                        $this->sessionVars['CURRENT_SCHEMA'] = $configuration['schema'];
                    }

                    $db->setSessionVars($this->sessionVars);

                    return $db;
                });

            }

            $this->capsule->addConnection([
                'driver'   => $connection['driver'],
                'host'     => $connection['host'],
                'port'     => $connection['port'],
                'database' => $connection['database'],
                'username' => $connection['username'],
                'password' => $connection['password'],
                'charset'  => $connection['charset'],
                'prefix'   => $connection['prefix'],
            ], $key);

        }

        $this->capsule->setEventDispatcher(new Dispatcher(new Container));

        $this->capsule->setAsGlobal();

        $this->capsule->bootEloquent();

        return $this->capsule;

    }

    public function getCapsule(): Capsule
    {
        return $this->capsule;
    }
}