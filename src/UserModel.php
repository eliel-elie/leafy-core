<?php

namespace LeafyTech\Core;


abstract class UserModel
{
    public string $userid;
    public string $name;
    public string $email;
    public string $department;
    public string $title;
    public ?string $avatar;
    public ?string $initials;
    public array $groups = [];

    public function getInitials(): string
    {
        $numberParts = explode(" ", $this->name);

        $lastName = "";
        $firstName= substr($numberParts[0],0,1);

        if (count($numberParts) > 1) {
            $lastName = substr($numberParts[count($numberParts)-1],0,1);
        }

        return strtoupper($firstName.$lastName);
    }

    public function getAvatar()
    {
        if($this->hasAvatar($this->userid)) {
            return Application::$app->config->ldap['photoUrl'] . DIRECTORY_SEPARATOR . $this->userid . ".jpg";
        }
    }

    public function hasAvatar(?string $userid) : bool
    {
        $userid  = $userid ?? $this->userid;
        $url     = Application::$app->config->ldap['photoUrl'] . DIRECTORY_SEPARATOR . strtoupper($userid). ".jpg";
        $headers = @get_headers($url);

        return ($headers && strpos( $headers[0], '200')) ?? false;

    }
}