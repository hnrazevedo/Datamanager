<?php

namespace HnrAzevedo\Datamanager;

use Exception;

abstract class Datamanager
{
    use DataTrait;
    
    private array $where = [''=> ["1",'=',"1"] ];

    private function mountTable_Field(string $field, $value = null)
    {
        $this->$field = null;
    }

    private function mountTable_Type(string $field, $value = null)
    {
        $type = $value;
        $maxlength = null;

        if(strpos($value,'(')){
            $type = (in_array( substr($value, 0, strpos($value,'(')) , ['varchar','char','text'])) ? 'string' : $type;
            $type = (in_array( substr($value, 0, strpos($value,'(')) , ['tinyint','mediumint','smallint','bigtint','int'])) ? 'int' : $type;
            $type = (in_array( substr($value, 0, strpos($value,'(')) , ['decimal','float','double','real'])) ? 'float' : $type;
        }

        $maxlength = (in_array( $type , ['string','float','int'])) ? substr($value,(strpos($value,'(')+1),-1) : $maxlength;
        $maxlength = (in_array( $type , ['date'])) ? 10 : $maxlength;
        $maxlength = (in_array( $type , ['datetime'])) ? 19 : $maxlength;
        $maxlength = (in_array( $type , ['boolean'])) ? 1 : $maxlength;

        $this->$field = ['maxlength' => $maxlength];
        $this->$field = ['type' => $type];
    }

    private function mountTable_Null(string $field, $value = null)
    {
        $this->$field = ['null' => ($value === 'YES') ? 1 : 0];
    }

    private function mountTable_Key(string $field, $value = null)
    {
        $this->$field = ['key' => $value];
        $this->$field = ['upgradeable' => ($value == 'PRI') ? 0 : 1];
    }

    private function mountTable_Extra(string $field, $value = null)
    {
        $this->$field = ['extra' => $value];
    }

    private function mountTable_Default(string $field, $value = null)
    {
        $this->$field = ['default' => $value];
        $this->$field = ['value' => null];
        $this->$field = ['changed' => false];
        $this->select[$field] = true;
    }

    private function mountData(array $table): Datamanager
    {
        foreach ($table as $column) {
            foreach ($column as $propriety => $value) {
                $method = "mountTable_{$propriety}";
                $this->$method($column['Field'], $value);
            }
        }
        return $this;
    }

    public function check_where_array(array $where)
    {
        if(count($where) != 3){
            throw new Exception("Condition where set incorrectly: ".implode(' ',$where));
        }

        if(!array_key_exists($where[0],$this->data) && $this->full){
            throw new Exception("{$where[0]} field does not exist in the table {$this->table}.");
        }
    }

    private function mountRemove(): array
    {
        $return = ['data' => [], 'where' => ''];
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

    public function save(): Datamanager
    {
        $this->transaction('begin');

        try{
            $this->update(
                $this->mountSave()['data'],
                "{$this->primary}=:{$this->primary}", 
                $this->primary.'='.$this->getData()[$this->primary]['value']
            );

            $this->check_fail();

            $this->transaction('commit');
        }catch(Exception $er){
            $this->transaction('rollback');
            throw $er;
        }

        return $this;
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
