<?php

namespace LeafyTech\Core\Helpers;


use InvalidArgumentException;

class Ldap
{
    public const TYPE = 'LDAP';
    /**
     * The active LDAP connection.
     */
    protected $connection;

    /**
     * The custom configuration.
     */
    protected $configuration;

    /**
     * The bound status of the connection.
     *
     * @var bool
     */
    protected $bound = false;

    /**
     * @var array|false
     */
    protected $info;

    public function __construct($configuration)
    {

        if (is_array($configuration)) {
            $this->configuration = $configuration;
            return $this;
        }

        throw new InvalidArgumentException(
            "Configuration must be array"
        );

    }

    public function connect()
    {
        $hosts = explode(',',$this->configuration['host']);

        foreach ($hosts as $host) {

            $this->connection = ldap_connect($host, $this->configuration['port']);

            if($this->connection) {
                foreach ($this->prepareOptions() as $option => $value) {
                    $this->setOption($option, $value);
                }
                return $this;
            }
        }

        throw new Exception('Failed to connect to LDAP hosts.');

    }

    public function close()
    {
        $connection = $this->connection;
        return is_resource($connection) && ldap_close($connection);

    }

    public function memberOf($username = null, $devPrefix = null, $appPrefix = null)
    {
        if(!$this->bound) $this->bindAsAdministrator();

        if(is_null($username)) $username = $this->userId();

        $resultSearch = $this->search($this->configuration['baseDn'], "sAMAccountName=".$username, ['memberof']);
        $results      = $this->getEntries($resultSearch);
        $memberOf     = [];

        foreach($results[0]['memberof'] as $value){
            $group = substr($value, 3, strpos($value, ",") - 3);
            if(!is_null($devPrefix) && !is_null($appPrefix)) {
                if(substr($group,0,9) == $devPrefix) {
                    $permission = explode('_', $group);
                    if(in_array($appPrefix, $permission)) {
                        $memberOf[] = $group;
                    }
                }
            } else {
                $memberOf[] = $group;
            }
        }
        $this->freeResult($resultSearch);

        return $memberOf;
    }

    public function userId()
    {
        if(!is_null($this->info)) {
            return $this->info[0]['samaccountname'][0];
        }
        return;
    }

    public function displayName()
    {
        if(!is_null($this->info)) {
            return $this->info[0]['displayname'][0];
        }
        return;
    }

    public function getDepartmentName()
    {
        if(!is_null($this->info)) {
            return $this->info[0]['department'][0];
        }
        return;
    }

    public function getEmail()
    {
        if(!is_null($this->info)) {
            return $this->info[0]['mail'][0] ?? '';
        }
        return;
    }

    public function getPersonalTitle()
    {
        if(!is_null($this->info)) {
            return $this->info[0]['title'][0];
        }
        return;
    }

    public function getInfo($username = null)
    {
        if (!is_null($username)){
            if (!$this->bound) $this->bindAsAdministrator();
            $resultSearch = $this->search($this->configuration['baseDn'], "sAMAccountName=" . $username, ['*']);
            return $this->getEntries($resultSearch);
        }
        return $this->info;
    }

    public function authenticate($username, $password)
    {
        try {

            $this->validateCredentials($username, $password);

            if(@ldap_bind($this->connection, $this->applyPrefix($username), html_entity_decode($password))) {
                $this->bound  = true;
                $resultSearch = $this->search($this->configuration['baseDn'], "sAMAccountName=".$username, ['*']);
                $this->info   = $this->getEntries($resultSearch);
            }

        } catch (\Exception $e) {
            $this->bound = false;
        }

        return $this->bound;
    }

    public function setOption($option, $value)
    {
        return ldap_set_option($this->connection, $option, $value);
    }

    /**
     * Validates the specified username and password from being empty.
     *
     * @param string $username
     * @param string $password
     */
    protected function validateCredentials($username, $password)
    {
        if (empty($username)) {
            throw new Exception('A username must be specified.');
        }

        if (empty($password)) {
            throw new Exception('A password must be specified.');
        }
    }

    public function errNo()
    {
        return ldap_errno($this->connection);
    }

    public function getDiagnosticMessage()
    {
        ldap_get_option($this->connection, LDAP_OPT_ERROR_STRING, $message);

        return $message;
    }

    public function search($dn, $filter, array $fields, $onlyAttributes = false, $size = 0, $time = 0)
    {
        return ldap_search($this->connection, $dn, $filter, $fields, $onlyAttributes, $size, $time);
    }

    public function getEntries($searchResults)
    {
        return ldap_get_entries($this->connection, $searchResults);
    }

    public function freeResult($result)
    {
        return ldap_free_result($result);
    }

    protected function applyPrefix($username)
    {
        $prefix = $this->configuration['accountPrefix'];
        return $prefix.'\\'.$username;
    }

    public function bindAsAdministrator()
    {
        $this->authenticate($this->configuration['username'], $this->configuration['password']);
    }

    protected function prepareOptions()
    {
        return array_replace(
            [
                LDAP_OPT_PROTOCOL_VERSION => $this->configuration['options']['version'],
                LDAP_OPT_REFERRALS        => $this->configuration['options']['followReferrals'],
            ]
        );
    }
}
