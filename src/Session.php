<?php

namespace LeafyTech\Core;

use LeafyTech\Core\Helpers\FlashMessages;

class Session
{
    protected const FLASH_KEY = 'flash_messages';

    private FlashMessages $msg;

    public function __construct()
    {
        session_start();
        $this->msg = new FlashMessages();

    }

    public static function getId(): string
    {
        return session_id();
    }

    public function set($key, $value)
    {
        $_SESSION[$key] = $value;
    }

    public function get($key)
    {
        return $_SESSION[$key] ?? false;
    }

    public function remove($key)
    {
        unset($_SESSION[$key]);
    }

    public function flashMessage(): FlashMessages
    {
        return $this->msg;
    }

    public function __destruct()
    {
        $this->removeFlashMessages();
    }

    private function removeFlashMessages()
    {
        $flashMessages = $_SESSION[self::FLASH_KEY] ?? [];
    }
}