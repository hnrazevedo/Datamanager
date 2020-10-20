<?php

namespace HnrAzevedo\Datamanager;

use HnrAzevedo\Datamanager\DatamanagerException;

trait CheckTrait{

    protected function check_where_array(array $where): void
    {
        if(count($where) != 3){
            throw new DatamanagerException("Condition where set incorrectly: ".implode(' ',$where));
        }

        if(!array_key_exists($where[0],$this->data) && $this->full){
            throw new DatamanagerException("{$where[0]} field does not exist in the table {$this->table}.");
        }
    }

    protected function isSettable(string $prop): void
    {
        if($this->full && !array_key_exists($prop,$this->data)){
            throw new DatamanagerException("{$prop} field does not exist in the table {$this->table}.");
        }
    }

    protected function checkLimit(): void
    {
        if(is_null($this->limit)){
            throw new DatamanagerException("The limit must be set before the offset.");
        }
    }

    protected function checkMaxlength(string $field, $val , $max): void
    {
        if(strlen($val) > $max){
            throw new DatamanagerException("The information provided for column {$field} of table {$this->table} exceeded that allowed.");
        }
    }

    protected function checkSettable(string $field, $val , $max): void
    {   
        if($this->options['maxlength']){
            $this->checkMaxlength($field, $val, $max);
        }
    }

    protected function upgradeable(string $field): bool
    {
        return (($this->data[$field]['changed'] && $this->data[$field]['upgradeable']) || $this->primary === $field);
    }

    protected function isIncremented(string $field): bool
    {
        return ( strstr($this->data[$field]['extra'],'auto_increment') && $field === $this->primary );
    }

    protected function checkForChanges(): bool
    {
        $hasChanges = false;
        foreach($this->data as $data){
            if($data['changed']){
                $hasChanges = true;
            }
        }
        if(!$hasChanges){
            throw new DatamanagerException('There were no changes to be saved in the database.');
        }
        return true;
    }

    protected function checkUniques($data): void
    {
        foreach($this->data as $d => $dd){
            if($dd['key'] === 'UNI'){
                $exist = $this->find()->where([$d,'=',$data[$d]])->only('id')->execute()->getCount();
                if($exist > 0){
                    throw new DatamanagerException("A record with the same {$this->getField($d)} already exists.");
                }
            }
        }
    }

    protected function checkNull(array $data): void
    {
        foreach($this->data as $input => $attr){
            if(!array_key_exists($input, $data)){
                continue;
            }
            if(($attr['null'] === 0) && (null === $data[$input])){
                throw new DatamanagerException("{$input} cannot be null");
            }
        }
    }
}
