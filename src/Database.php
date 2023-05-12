<?php

namespace LeafyTech\Core;

use Illuminate\Container\Container;
use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Events\Dispatcher;
use Yajra\Oci8\Connectors\OracleConnector;
use Yajra\Oci8\Oci8Connection;

class Database
{
    protected array $sessionVars = [
        'NLS_TIME_FORMAT'         => 'HH24:MI:SS',
        'NLS_DATE_FORMAT'         => 'DD/MM/YYYY',
        'NLS_TIMESTAMP_FORMAT'    => 'DD/MM/YYYY HH24:MI:SS',
        'NLS_TIMESTAMP_TZ_FORMAT' => 'DD/MM/YYYY HH24:MI:SS TZH:TZM',
        'NLS_NUMERIC_CHARACTERS'  => '.,',
    ];

    protected Capsule $capsule;

    public function __construct($config)
    {
        $this->capsule = new Capsule;

        if($config->db['driver'] === 'oracle') {

            $manager = $this->capsule->getDatabaseManager();

            $manager->extend('oracle', function($configuration) use ($config)
            {
                $connector = new OracleConnector();
                $connection = $connector->connect($configuration);
                $db = new Oci8Connection($connection, $config->db['database'], $config->db["prefix"]);

                if (isset($configuration['schema'])) {
                    $this->sessionVars['CURRENT_SCHEMA'] = $configuration['schema'];
                }

                $db->setSessionVars($this->sessionVars);

                return $db;
            });

        }

        $this->capsule->addConnection([
            'driver'   => $config->db['driver'],
            'host'     => $config->db['host'],
            'port'     => $config->db['port'],
            'database' => $config->db['database'],
            'username' => $config->db['username'],
            'password' => $config->db['password'],
            'charset'  => $config->db['charset'],
            'prefix'   => $config->db['prefix'],
        ]);

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