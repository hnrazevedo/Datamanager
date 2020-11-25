<?php

namespace HnrAzevedo\Datamanager;

use Exception;

class DatamanagerException extends Exception
{
    
    public function __construct($message, $code = 0, Exception $previous = null)
    {
        parent::__construct($message, intval($code), $previous);
    }

    public function __toString()
    {
        return __CLASS__ . ": [{$this->code}]: {$this->message}\n";
    }

}