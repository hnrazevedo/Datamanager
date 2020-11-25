<?php

namespace HnrAzevedo\Datamanager;

use HnrAzevedo\Datamanager\DatamanagerException;

class Datamanager
{
    use DataTrait,
        SynchronizeTrait,
        EntityTrait,
        MagicsTrait,
        DebugTrait;

    protected ?string $table = null;
    protected ?string $primary = null;
    protected array $data = [];
    protected array $where = [''=> ["1",'=',"1"] ];
    protected array $between = [];

    public function getData(): ?array
    {
        return $this->data;
    }

    public function getCount(): int
    {
        return $this->count;
    }

    public function except($deniable): Datamanager
    {
        $deniable = (is_array($deniable)) ? $deniable : [$deniable];

        foreach ($deniable as $field) {
            if(!array_key_exists($field,$this->data)){
                throw new DatamanagerException("{$field} " . self::$DATAMANAGER_LANG['fieldNotFound'] . " {$this->table}.");
            }
            $this->excepts[$field] = true;
        }

        return $this;
    }

    public function deny(): Datamanager
    {
        foreach ($this->excepts as $field => $value) {
            unset($this->select[$field]);
        }
        return $this;
    }

    public function orderBy(string $field, string $ord = 'ASC'): Datamanager
    {
        $this->isSettable( str_replace(['asc', 'ASC', 'desc', 'DESC', ' '], '', $field) );

        $ord = (strpos(strtolower($field), 'asc') || strpos(strtolower($field), 'desc')) ? '' : $ord;

        $this->order = " ORDER BY {$field} {$ord} ";
        return $this;
    }

    public function only($params): Datamanager
    {
        $params = (is_array($params)) ? $params : [$params];
        $this->select = [];

        foreach ($params as $field) {
            $this->isSettable($field);
            $this->select[$field] = true;
        }

        if(!is_null($this->primary)){
            $this->select[$this->primary] = true;
        }
        return $this;
    }

    public function where(array $where): Datamanager
    {
        $this->where['AND'] = (isset($this->where['AND'])) ? $this->where['AND'] : [] ;
        $w = [];
        foreach ($where as $condition => $values) {

            if(!is_array($values)){
                $w['AND'][] = $values;
                continue;
            }

            $this->checkWhereArray($values);

            $w[(is_int($condition) ? 'AND' : $condition)][] = $values;
                       
        }
        
        $this->setWhere($w);

        return $this;
    }

    private function setWhere(array $where): void
    {
        $this->where['AND'] = array_merge($this->where['AND'],$where['AND']);

        if(array_key_exists('OR', $where)){
            $this->where['OR'] = array_merge( (isset($this->where['OR'])) ? [] : $this->where['OR'] , $where['OR']);
        }
    }

    public function between(array $bet): Datamanager
    {
        $this->between = array_merge($this->between, $bet);
        return $this;
    }

    public function limit(string $limit): Datamanager
    {
        $this->limit = $limit;
        return $this;
    }

    public function offset(int $offset): Datamanager
    {
        $this->checkLimit();
        $this->offset = $offset;
        return $this;
    }

    public function result(): array
    {
        return $this->result;
    }

    public function first(): Datamanager
    {
        return  (count($this->result) > 0) ? $this->setByDatabase($this->result[0]) : $this;
    }

    public function setByDatabase(array $arrayValues): Datamanager
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

            $string .= '"'.$key.'"'.':"'.$this->$key.'",';
        }
        return str_replace(',}', '}', '{'.$string.'}');
    }

    private function removeById(): bool
    {
        $delete = $this->delete("{$this->primary}=:{$this->primary}","{$this->primary}={$this->getData()[$this->primary]['value']}");

        $this->check_fail();

        return $delete;
    }

    public function findById($id): Datamanager
    {
        return $this->where([
            [$this->primary, '=', $id]
        ]);
    }

    public function execute(): Datamanager
    {
        if(!is_null($this->clause) && $this->clause == 'remove'){
            return $this->remove(true);
        }

        $this->deny();
        
        $this->mountSelect();
        
        $where = substr($this->mountWhereExec()['where'],0,-1);
        $where .= substr($this->mountBetweenExec()['where'],0,-1);

        $this->query .= " WHERE {$where} ";

        $this->query .= $this->order;
       
        $this->mountLimit();
        $this->mountOffset();

        
        $dataSelect = array_merge($this->mountWhereExec()['data'], $this->mountBetweenExec()['data']);

        $this->result = $this->select(
            $this->query, 
            $dataSelect
        );

        $this->check_fail();

        $this->count = count($this->result);
        $this->query = null;
        $this->where = [''=> ["1",'=',"1"] ];

        return $this;
    }

    public function find(?int $key = null): Datamanager
    {
        $this->query = " SELECT * FROM {$this->table} ";
        return (is_int($key)) ? $this->findById($key) : $this;
    }
    
    
}
