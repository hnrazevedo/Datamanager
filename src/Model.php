<?php

namespace HnrAzevedo\Datamanager;

use HnrAzevedo\Datamanager\Datamanager;

class Model extends Datamanager
{
    protected array $fields = [];

    public function create(string $table, ?string $prikey = null)
    {
        parent::synchronize($table, $prikey);
    }
    
    public function getField(string $name)
    {
        return array_key_exists($name, $this->fields) ? $this->fields[$name] : $name;
    }

    protected function clone(?array $clone = null): array
    {
        if(null !== $clone){
            $this->table = $clone['table'];
            $this->primary = $clone['primary_key'];
            $this->fields($clone['fields']);
            $this->select = $clone['select'];
            $this->data = $clone['data'];
        }

        return [
            'table' => $this->table,
            'primary_key' => $this->primary,
            'fields' => $this->fields(),
            'select' => $this->select,
            'data' => $this->data
        ];
    }

    protected function fields(?array $fields = null): array
    {
        if(null !== $fields){
            $this->fields = $fields;
        }
        return $this->fields;
    }
}
