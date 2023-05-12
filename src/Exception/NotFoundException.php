<?php

namespace LeafyTech\Core\Exception;

class NotFoundException extends \Exception
{
    protected $message = 'Page not found';
    protected $code    = 404;
}