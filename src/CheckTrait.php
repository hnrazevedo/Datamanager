<?php

namespace HnrAzevedo\Datamanager;

use HnrAzevedo\Datamanager\DatamanagerException;

trait CheckTrait{

    protected function check_where_array(array $where)
    {
        if(count($where) != 3){
            throw new DatamanagerException("Condition where set incorrectly: ".implode(' ',$where));
        }

        if(!array_key_exists($where[0],$this->data) && $this->full){
            throw new DatamanagerException("{$where[0]} field does not exist in the table {$this->table}.");
        }
    }

    protected function isSettable(string $prop)
    {
        if($this->full && !array_key_exists($prop,$this->data)){
            throw new DatamanagerException("{$prop} field does not exist in the table {$this->table}.");
        }
    }

    protected function checkLimit()
    {
        if(is_null($this->limit)){
            throw new DatamanagerException("The limit must be set before the offset.");
        }
    }

    protected function checkMaxlength(string $field, $val , $max)
    {
        if(strlen($val) > $max){
            throw new DatamanagerException("The information provided for column {$field} of table {$this->table} exceeded that allowed.");
        }
    }

    protected function upgradeable(string $field): bool
    {
        return (($this->data[$field]['changed'] && $this->data[$field]['upgradeable']) || $this->primary === $field);
    }

    protected function isIncremented(string $field): bool
    {
        return ( strstr($this->data[$field]['extra'],'auto_increment') && $field !== $this->primary );
    }
}
