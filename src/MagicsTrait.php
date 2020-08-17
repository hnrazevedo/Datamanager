<?php

namespace HnrAzevedo\Datamanager;

trait MagicsTrait{

    public function __set(string $prop,$value)
    {
        if(is_array($value)){
            $attr = array_keys($value)[0];
            $this->data[$prop][$attr] = $value[$attr];
            return $this;
        }

        if($this->full){
            switch($this->data[$prop]['type']){
                case 'date':
                    $value = (date_format( date_create_from_format(DATAMANAGER_CONFIG['dateformat'],$value) , 'Y-m-d'));
                    break;
            }
        }

        $this->isSettable($prop);

        $this->data[$prop]['changed'] = true;
        $this->data[$prop]['value'] = $value;
        
        return $this;
    }

    public function __get(string $field)
    {
        $this->isSettable($field);

        if($this->full){
            switch($this->data[$field]['type']){
                case 'date': 
                    return (!empty($this->data[$field]['value'])) ? (@date_format( @date_create_from_format('Y-m-d' , $this->data[$field]['value'] ) , DATAMANAGER_CONFIG['dateformat'])) : null ;
                    break;
                case 'datetime': 
                    return (!empty($this->data[$field]['value'])) ? (@date_format( @date_create_from_format('Y-m-d h:i:s' , $this->data[$field]['value'] ) , DATAMANAGER_CONFIG['datetimeformat'])) : null ;
                    break;
            }
        }

        return $this->data[$field]['value'];
    }
}