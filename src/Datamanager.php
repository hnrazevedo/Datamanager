<?php

namespace HnrAzevedo\Datamanager;

abstract class Datamanager
{
    use DataTrait, SynchronizeTrait;

    protected ?string $table = null;
    protected ?string $primary = null;
    protected array $data = [];

    
    private array $where = [''=> ["1",'=',"1"] ];


    private function mountRemove(): array
    {
        $return = ['data' => '', 'where' => ''];
        foreach($this->where as $clause => $condition){
            if(strlen($clause) === 0){
                $return['where'] .= " {$clause} {$condition[0]} {$condition[1]} :q_{$condition[0]} ";
                $return['data'] .= "q_{$condition[0]}={$condition[2]}&";
                continue;
            }
                
            foreach($condition as $value){
                $return['where'] .= " {$clause} {$value[0]} {$value[1]} :q_{$value[0]} ";
                $return['data'] .= "q_{$value[0]}={$value[2]}&";
            }
        }
        return $return;
    }   

    private function mountSave(): array
    {
        $return = ['data' => []];

        foreach ($this->data as $key => $value) {
            if(strstr($this->data[$key]['extra'],'auto_increment') && $key !== $this->primary){
                continue;
            }

            if(($this->data[$key]['changed'] && $this->data[$key]['upgradeable']) || $this->primary === $key){
                $return['data'][$key] = $this->data[$key]['value'];
            }
        }

        return $return;
    }

    

    private function mountWhereExec(): array
    {
        $return = ['where' => '', 'data' => []];

        foreach ($this->where as $key => $value) {

            $key = (!$key) ? '' : " {$key} ";

            if(is_array($value[0])){

                foreach ($value as $k => $v) {
                    $return['where'] .= " {$key} {$v[0]} {$v[1]} :q_{$v[0]} ";
                    $return['data']["q_{$v[0]}"] = $v[2];
                }

                continue;
            }
             
            $return['where'] .= " {$key} {$value[0]} {$value[1]} :q_{$value[0]} ";
            $return['data']["q_{$value[0]}"] = $value[2];

        }
        return $return;
    }

    private function mountSelect()
    {
        $select = implode(',',array_keys($this->select));

        $this->query = str_replace('*', $select,$this->query);
    }

    private function mountLimit()
    {
        if(!is_null($this->limit)){
            $this->query .= " LIMIT {$this->limit}";
        }
    }

    private function mountOffset()
    {
        if(!is_null($this->offset)){
            $this->query .= " OFFSET {$this->offset}";
        }
    }

}
