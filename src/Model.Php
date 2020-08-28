<?php

namespace HnrAzevedo\Datamanager;

class Model extends Datamanager
{
    protected array $fields = [];

    public function create(string $table, ?string $prikey = null)
    {
        parent::synchronize($table,$prikey);
    }
    
    public function getField(string $name)
    {
        return array_key_exists($name,$this->fields) ? $this->fields[$name] : $name;
    }
}
