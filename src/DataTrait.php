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
        $c = -1;
        foreach($this->where as $clause => $condition){
            $c++;
            if(strlen($clause) === 0){
                $return['where'] .= " {$clause} {$condition[0]} {$condition[1]} :q_{$condition[0]}{$c} ";
                $return['data'] .= "q_{$condition[0]}={$condition[2]}{$c}&";
                continue;
            }
                
            foreach($condition as $value){
                $return['where'] .= " {$clause} {$value[0]} {$value[1]} :q_{$value[0]}{$c} ";
                $return['data'] .= "q_{$value[0]}={$value[2]}{$c}&";
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

    private function mountWheres(array $value, string $key, int $c): array
    {
        $return = [];
        for($i = 0; $i < count($value); $i++){
                
            if(!is_array($value[$i][2])){
                $return['where'] .= " {$key} {$value[$i][0]} {$value[$i][1]} :q_{$value[$i][0]}{$c}{$i} ";
                $return['data']["q_{$value[$i][0]}{$c}{$i}"] = $value[$i][2];
                continue;
            }

            $return['where'] .= " {$key} {$value[$i][0]} {$value[$i][1]} (";

            foreach($value[$i][2] as $v => $valu){
                $return['where'] .= " :q_{$value[$i][0]}{$c}{$i}_{$v},";
                $return['data']["q_{$value[$i][0]}{$c}{$i}_{$v}"] = $valu;
            }

            $return['where'] = substr($return['where'],0,-1) .') ';
        }
        return $return;
    }

    protected function mountWhereExec(): array
    {
        $return = ['where' => '', 'data' => []];
        $c = -1;
        foreach ($this->where as $key => $value) {
            $c++;
            $key = (!$key) ? '' : " {$key} ";

            if(!is_array($value[0])){
                $return['where'] .= " {$key} {$value[0]} {$value[1]} :q_{$value[0]}{$c} ";
                $return['data']["q_{$value[0]}{$c}"] = $value[2];
                continue;
            }

            $return = array_merge($return,$this->mountWheres($value, $key, $c));
            
        }
        return $return;
    }

    protected function mountBetweenExec(): array
    {
        $return = ['where' => '', 'data' => []];

        $c = -1;
        foreach($this->between as $field => $value){
            $c++;
            $condition = (count(explode(' ',$field)) > 2) ? ' '.explode(' ',$field)[0].' ' : ' AND ';
            $field = str_replace(['AND','OR',' '],'',$field);
            $return['where'] .= " {$condition} {$field} BETWEEN :q_1{$field}{$c} AND :q_2{$field}{$c} ";
            $return['data'] = [
                "q_1{$field}{$c}" => (date_format( date_create_from_format(DATAMANAGER_CONFIG['dateformat'],$value[0]) , 'Y-m-d')),
                "q_2{$field}{$c}" => (date_format( date_create_from_format(DATAMANAGER_CONFIG['dateformat'],$value[1]) , 'Y-m-d'))
            ];
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
