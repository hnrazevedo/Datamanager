<?php

namespace HnrAzevedo\Datamanager;

use HnrAzevedo\Datamanager\DatamanagerException;

abstract class Datamanager
{
    use DataTrait, SynchronizeTrait, EntityTrait;

    protected ?string $table = null;
    protected ?string $primary = null;
    protected array $data = [];
    protected array $where = [''=> ["1",'=',"1"] ];

    public function __set(string $prop,$value)
    {
        if(is_array($value)){
            $attr = array_keys($value)[0];
            $this->data[$prop][$attr] = $value[$attr];
            return $this;
        }

        $this->isSettable($prop);

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
        $this->isSettable($field);
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
                throw new DatamanagerException("{$field} field does not exist in the table {$this->table}.");
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
        $this->isSettable( str_replace(['asc','ASC','desc','DESC',' '],'',$field) );

        $ord = (strpos(strtolower($field),'asc') || strpos(strtolower($field),'desc')) ? '' : $ord;

        $this->order = " ORDER BY {$field} {$ord} ";
        return $this;
    }

    public function only($params)
    {
        $params = (is_array($params)) ? $params : [$params];
        $this->select = [];

        foreach ($params as $field) {

            $this->isSettable($field);

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
        $this->checkLimit();

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

            $this->isSettable($key);

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

    private function removeById(): bool
    {
        $delete = $this->delete("{$this->primary}=:{$this->primary}","{$this->primary}={$this->getData()[$this->primary]['value']}");

        $this->check_fail();

        return $delete;
    }

    public function findById($id)
    {
        return $this->where([$this->primary,'=',$id]);
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
        return (is_int($key)) ? $this->findById($key) : $this;
    }

    
    
}
