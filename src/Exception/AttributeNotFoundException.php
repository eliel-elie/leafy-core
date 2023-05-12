<?php

namespace LeafyTech\Core\Exception;

class AttributeNotFoundException extends \Exception
{
    protected $message = 'Empty array of attributes is required';
    protected $code    = 0;
}