<?php

namespace HnrAzevedo\Datamanager;

trait Typing
{

    protected function getMax(string $type, string $real): int
    {
        switch($type){
            case 'boolean': 
                return 1;
            case 'datetime': 
                return 19;
            case 'date': 
                return 10;
            case 'float':
                return (strpos($real, '(')) ? intval(substr($real,(strpos($real,'(')+1), (strpos($real,',')+1)  )) : 10;
            default:
                return intval(substr($real,(strpos($real,'(')+1),-1));
                break;
        }
    }

    protected function getType(string $typed): string
    {
        if(strpos($typed, '(')){
            $typed = (in_array( substr($typed, 0, strpos($typed,'(')) , ['varchar' ,'char' ,'text'])) ? 'string' : $typed;

            $typed = (in_array( substr($typed, 0, strpos($typed,'(')) , ['tinyint' ,'mediumint' ,'smallint' ,'bigint' ,'int'])) ? 'int' : $typed;

            $typed = (in_array( substr($typed, 0, strpos($typed,'(')) , ['decimal' ,'float' ,'double' ,'real'])) ? 'float' : $typed;
        }

        return $typed;
    }

}
