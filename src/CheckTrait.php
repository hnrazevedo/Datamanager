<?php

namespace HnrAzevedo\Datamanager;

use Exception;

trait CheckTrait{

    protected function check_where_array(array $where)
    {
        if(count($where) != 3){
            throw new Exception("Condition where set incorrectly: ".implode(' ',$where));
        }

        if(!array_key_exists($where[0],$this->data) && $this->full){
            throw new Exception("{$where[0]} field does not exist in the table {$this->table}.");
        }
    }

    protected function isSettable(string $prop)
    {
        if($this->full && !array_key_exists($prop,$this->data)){
            throw new Exception("{$prop} field does not exist in the table {$this->table}.");
        }
    }

    protected function checkLimit()
    {
        if(is_null($this->limit)){
            throw new Exception("The limit must be set before the offset.");
        }
    }

    protected function checkMaxlength(string $field, $val , $max)
    {
        if(strlen($val) > $max){
            throw new Exception("The information provided for column {$field} of table {$this->table} exceeded that allowed.");
        }
    }

}
