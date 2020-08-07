<?php

namespace HnrAzevedo\Datamanager;

trait DataTrait{
    use CrudTrait, CheckTrait;

    protected ?string $table = null;
    protected ?string $primary = null;
    protected array $data = [];
    protected bool $full = false;

    protected array $result = [];
    protected ?string $clause = null;

    protected ?string $order = null;
    protected ?string $limit = null;
    protected ?int $offset = null;
    protected array $excepts = [];
    protected int $count = 0;
    protected array $select = [];
    protected ?string $query = null;

    protected function mountRemove(): array
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

    protected function mountSave(): array
    {
        $return = ['data' => []];

        foreach ($this->data as $key => $value) {
            if($this->upgradeable($key) && !$this->isIncremented($key)){
                $return['data'][$key] = $this->data[$key]['value'];
            }
        }

        return $return;
    }

    protected function mountWhereExec(): array
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

    protected function mountSelect()
    {
        $select = implode(',',array_keys($this->select));

        $this->query = str_replace('*', $select,$this->query);
    }

    protected function mountLimit()
    {
        if(!is_null($this->limit)){
            $this->query .= " LIMIT {$this->limit}";
        }
    }

    protected function mountOffset()
    {
        if(!is_null($this->offset)){
            $this->query .= " OFFSET {$this->offset}";
        }
    }

}