<?php

namespace HnrAzevedo\Datamanager;

trait DebugTrait
{
    protected string $lastQuery = '';
    protected array $lastData = [];

    public function debug(bool $array = false)
    {
        if($array){
            return ['query' => $this->lastQuery, 'data' => $this->lastData];
        }
        
        $query = $this->lastQuery;

        foreach($this->lastData as $name => $value){
            $query = str_replace(":{$name}","'{$value}'",$query);
        }

        return $query;
    }    

    

}
