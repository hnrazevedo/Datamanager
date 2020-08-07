<?php

namespace HnrAzevedo\Datamanager;

use Exception;

trait DataTrait{
    protected ?string $table = null;
    protected ?string $primary = null;

    protected array $result = [];
    protected array $data = [];
    protected bool $full = false;
    protected ?string $clause = null;

    protected ?string $order = null;
    protected ?string $limit = null;
    protected ?int $offset = null;
    protected array $excepts = [];
    protected int $count = 0;
    protected array $select = [];
    protected ?string $query = null;

    protected function create(string $table, string $primary)
    {
        $this->table = $table;
        $this->primary = $primary;
        $describe = $this->describe();
        
        $this->check_fail();

        $this->mountData($describe);
        $this->full = true;
        return $this;
    }

    public function __set(string $prop,$value)
    {

        if(is_array($value)){
            $attr = array_keys($value)[0];
            $this->data[$prop][$attr] = $value[$attr];
            return $this;
        }

        if($this->full && !array_key_exists($prop,$this->data)){
            throw new Exception("{$prop} field does not exist in the table {$this->table}.");
        }

        $this->data[$prop]['changed'] = true;
        $this->data[$prop]['value'] = $value;
        
        return $this;
    }

    public function getData(): ?array
    {
        return $this->data;
    }

    public function __get(string $field)
    {
        if($this->full && !array_key_exists($field,$this->data)){
            throw new Exception("{$field} field does not exist in the table {$this->table}.");
        }

        return $this->data[$field]['value'];
    }

    public function getCount(): int
    {
        return $this->count;
    }

    public function except($deniable)
    {
        $deniable = (is_array($deniable)) ? $deniable : [$deniable];

        foreach ($deniable as $field) {
            if(!array_key_exists($field,$this->data)){
                throw new Exception("{$field} field does not exist in the table {$this->table}.");
            }

            $this->excepts[$field] = true;
        }

        return $this;
    }

    public function deny()
    {
        foreach ($this->excepts as $field => $value) {
            unset($this->select[$field]);
        }
        return $this;
    }

    public function orderBy(string $field, string $ord = 'ASC')
    {
        if(!array_key_exists(str_replace(['asc','ASC','desc','DESC',' '],'',$field),$this->data) && $this->full){
            throw new Exception("{$field} field does not exist in the table {$this->table}.");
        }

        if(strpos(strtolower($field),'asc') || strpos(strtolower($field),'desc')){
            $ord = '';
        }

        $this->order = " ORDER BY {$field} {$ord} ";
        return $this;
    }

    public function only($params)
    {
        $params = (is_array($params)) ? $params : [$params];
        $this->select = [];

        foreach ($params as $field) {

            if(!array_key_exists($field,$this->data) && $this->full){
                throw new Exception("{$field} field does not exist in the table {$this->table}.");
            }

            $this->select[$field] = true;
        }
        $this->select[$this->primary] = true;

        return $this;
    }

    public function where(array $where)
    {
        $this->where['AND'] = (array_key_exists('AND',$this->where)) ?? '';
        $w = [];
        foreach ($where as $condition => $values) {

            if(!is_array($values)){
                $w['AND'][] = $values;
                continue;
            }

            $this->check_where_array($values);

            $w[(is_int($condition) ? 'AND' : $condition)][] = $values;
                       
        }

        $this->where = array_merge($this->where,$w);

        return $this;
    }

    public function limit(string $limit)
    {
        $this->limit = $limit;
        return $this;
    }

    public function offset(int $offset)
    {
        if(is_null($this->limit)){
            throw new Exception("The limit must be set before the offset.");
        }

        $this->offset = $offset;
        return $this;
    }

    public function result(): array
    {
        return $this->result;
    }

    public function first()
    {
        return  (count($this->result) > 0) ? $this->setByDatabase($this->result[0]) : $this;
    }

    public function setByDatabase(array $arrayValues)
    {
        $clone = clone $this;
        
        $clone->result = [
            0 => $this->result[0]
        ];

        $clone->count = 1;

        foreach ($arrayValues as $key => $value) {

            if(!array_key_exists($key,$this->data)){
                throw new Exception("{$key} field does not exist in the table {$this->table}.");
            }

            $clone->data[$key]['value'] = $value;
        }
        return $clone;
    }

    public function toJson(): string
    {
        $string = '';
        foreach ($this->data as $key => $value) {

            if(gettype($value)==='object'){
                $value = $value->getData()[$this->primary]['value'];
            }

            $string .= '"'.$key.'"'.':"'.$value.'",';
        }
        return str_replace(',}', '}', '{'.$string.'}');
    }

    public function remove(?bool $exec = false)
    {
        if(!$exec){
            $this->clause = 'remove';    
            return $this;
        }

        $this->clause = null;

        if(count($this->where) == 1){
            $this->removeById();
            return $this;
        }

        $this->delete($this->mountRemove()['where'], substr( $this->mountRemove()['data'] ,0,-1) );

        $this->check_fail();
            
        return $this;
    }

    private function removeById(): bool
    {
        $delete = $this->delete("{$this->primary}=:{$this->primary}","{$this->primary}={$this->getData()[$this->primary]['value']}");

        $this->check_fail();

        return $delete;
    }

    public function persist()
    {
        $columns = '';
        $values = '';
        $data = [];

        foreach ($this->data as $key => $value) {
            if(strstr($this->data[$key]['extra'],'auto_increment')){
                continue;
            }

            if(strlen($value['value']) > $value['maxlength']){
                throw new Exception("The information provided for column {$key} of table {$this->table} exceeded that allowed.");
            }

            $columns .= $key.',';
            $values .= ':'.$key.',';
            $data[$key] = $value['value'];
        }

        $this->transaction('begin');
        try{
           
            $id = $this->insert($data);

            $this->check_fail();

            $this->getData()[$this->primary]['value'] = $id;
            
            $this->transaction('commit');

        }catch(Exception $er){
            $this->transaction('rollback');
            throw $er;
        }

        return $this;
    }

    public function toEntity()
    {
        if($this->getCount() === 0){
            return null;
        }

        $entity = $this->setByDatabase($this->result[0]);

        if(count($this->result) > 1){
            $entity = [];
            foreach ($this->result as $key => $value) {
                $entity[] = $this->setByDatabase($value);
            }
        }

        return $entity;
    }

    public function findById($id)
    {
        $this->where([$this->primary,'=',$id]);
        return $this;
    }

    public function execute()
    {
        if(!is_null($this->clause) && $this->clause == 'remove'){
            return $this->remove(true);
        }

        $this->deny();
        
        $this->mountSelect();
        
        $where = substr($this->mountWhereExec()['where'],0,-1);
        $this->query .= " WHERE {$where} ";

        $this->query .= $this->order;
       
        $this->mountLimit();
        $this->mountOffset();

        $this->result = $this->select($this->query, $this->mountWhereExec()['data']);

        $this->check_fail();

        $this->count = count($this->result);
        $this->query = null;

        return $this;
    }

    public function find(?int $key = null)
    {
        $this->query = " SELECT * FROM {$this->table} ";

        if(is_int($key)){
            return $this->findById($key);
        }

        return $this;
    }

}