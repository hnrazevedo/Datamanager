<?php

namespace HnrAzevedo\Datamanager;

use Exception;

class Datamanager
{
    use CrudTrait;

    private ?Exception $fail = null;
    private ?string $table = null;
    private ?string $primary = null;
    private array $result = [];
    protected array $data = [];
    
    private bool $full = false;


    private array $where = [0=> ["1",'=',"1"] ];
    private ?string $order = null;
    private ?int $limit = null;
    private ?int $offset = null;
    private array $excepts = [];
    private int $count = 0;
    private array $select = [];
    private ?string $query = null;


    protected function create(string $table, string $primary): Datamanager
    {
        $this->table = $table;
        $this->primary = $primary;
        $this->mountData($this->describe());
        $this->full = true;
        return $this;
    }

    private function mountData(array $table): Datamanager
    {
        foreach ($table as $column) {
            $field = null;
            foreach ($column as $propriety => $value) {
                switch ($propriety) {
                    case 'Field':
                        $field = $value;
                        $this->$field = null;
                        break;
                    case 'Type':
                        $type = $value;

                        if(strpos($value,'(')){
                            switch (substr($value,0,strpos($value,'('))) {
                                case 'varchar':
                                case 'char':
                                case 'text': $type = 'string'; break;
                                case 'tinyint':
                                case 'mediumint':
                                case 'smallint':
                                case 'bigint':
                                case 'int': $type = 'int'; break;
                                case 'decimal':
                                case 'float':
                                case 'double':
                                case 'real': $type = 'float'; break;
                                default: $type = $value; break;
                            }
                        }

                        switch ($type) {
                            case 'string':
                            case 'float':
                            case 'int':
                                $this->$field = ['maxlength' => substr($value,(strpos($value,'(')+1),-1) ]; 
                                break;
                            case 'date':
                                $this->$field = ['maxlength' => 10];
                                break;
                            case 'datetime':
                                $this->$field = ['maxlength' => 19];
                                break;
                            case 'boolean':
                                $this->$field = ['maxlength' => 1];
                                break;
                            default:
                                $this->$field = ['maxlength' => null];
                                break;
                        }

                        $this->$field = ['type' => $type];
                        break;
                    case 'Null':
                        $this->$field = ['null' => ($value === 'YES') ? 1 : 0];
                        break;
                    case 'Key':
                        $this->$field = ['key' => $value];
                        $this->$field = ['upgradeable' => ($value == 'PRI') ? 0 : 1];
                        break;
                    case 'Extra':
                        $this->$field = ['extra' => $value];
                        break;
                    case 'Default':
                        $this->$field = ['default' => $value];
                        $this->$field = ['value' => null];
                        $this->$field = ['changed' => false];
                        $this->select[$field] = true;
                        break;
                }
            }
        }
        return $this;
    }

    public function __set(string $prop,$value): Datamanager
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

    public function except($deniable): Datamanager
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

    public function deny(): Datamanager
    {
        foreach ($this->excepts as $field => $value) {
            unset($this->select[$field]);
        }
        return $this;
    }

    public function orderBy(string $field, string $ord = 'ASC'): Datamanager
    {
        if(!array_key_exists($field,$this->data) && $this->create){
            throw new Exception("{$field} field does not exist in the table {$this->table}.");
        }

        if(strpos(strtolower($field),'asc') || strpos(strtolower($field),'desc')){
            $ord = '';
        }

        $this->order = " ORDER BY {$field} {$ord} ";
        return $this;
    }

    public function only($params): Datamanager
    {
        $params = (is_array($params)) ? $params : [$params];
        $this->select = [];
        foreach ($params as $field) {
            if(!array_key_exists($field,$this->data) && $this->create){
                throw new Exception("{$field} field does not exist in the table {$this->table}.");
            }

            $this->select[$field] = true;
        }

        return $this;
    }

    public function where(array $where): Datamanager
    {
        $this->where['AND'] = (array_key_exists('AND',$this->where)) ?? '';
        $w = [];
        foreach ($where as $condition => $values) {
            if(is_array($values)){
                if(count($values) != 3){
                    throw new Exception("Condition where set incorrectly: ".implode(' ',$values));
                }

                if(!array_key_exists($values[0],$this->data) && $this->create){
                    throw new Exception("{$values[0]} field does not exist in the table {$this->table}.");
                }

                $this->where[$condition] = $values;

                return $this;
            }
            $w[] = $values;
        }

        $this->where['AND'][] = $w;

        return $this;
    }

    public function limit(int $limit): Datamanager
    {
        $this->limit = $limit;
        return $this;
    }

    public function offset(int $offset): Datamanager
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

    public function first(): Datamanager
    {
        return  (count($this->result) > 0) ? $this->setByDatabase($this->result[0]) : $this;
    }

    public function setByDatabase(array $arrayValues): Datamanager
    {
        $clone = $this->clone();
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

    public function remove(): bool
    {
        return true;
    }

    public function clone(): Datamanager
    {
        $clone = clone $this;
        $clone->destroy();
        return $clone;
    }

    public function save(): Datamanager
    {
        $data = [];
        foreach ($this->data as $key => $value) {
            if($this->data[$key]['key'] === 'PRI' || strstr($this->data[$key]['extra'],'auto_increment')){
                continue;
            }

            if($this->data[$key]['changed'] && $this->data[$key]['upgradeable']){
                $data[$key] = $this->data[$key]['value'];
            }

        }


        $terms = "{$this->primary}=:{$this->primary}";
        $params = $this->primary.'=:'.$this->getData()[$this->primary]['value'];

        $this->transaction('begin');
        try{
            $this->update($data, $terms, $params);
            $this->transaction('commit');
        }catch(Exception $er){
            $this->transaction('rollback');
            throw $er;
        }

        return $this;
    }

    public function persist(): Datamanager
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

        $columns = substr($columns,0,-1);
        $values = substr($values,0,-1);

        $this->transaction('begin');
        try{
           
            $id = $this->insert($data);

            if(!is_null($this->fail)){
                throw $this->fail;
            }

            $this->primary = $id;
            
            $this->transaction('commit');

        }catch(Exception $er){
            $this->transaction('rollback');
            throw $er;
        }

        return $this;
    }

    public function toEntity()
    {
        $entity = null;

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

    public function findById($id): Datamanager
    {
        $pri = null;
        foreach ($this->data as $key => $value) {
            if($this->data[$key]['key'] === 'PRI'){
                $pri = $key;
            }
        }

        if(is_null($pri)){
            throw new Exception("The {$this->table} table has no primary key");
        }

        $this->where([$pri,'=',$id]);

        return $this;
    }



    

    public function execute(): Datamanager
    {
        $this->deny();
        $select = '';

        foreach ($this->select as $key => $value) {
            $select .= "{$key},";
        }
        $select = substr($select,0,-1);
        $this->query = str_replace("*",$select,$this->query);

        $where = '';
        $whereData = [];
        foreach ($this->where as $key => $value) {
            $key = (!$key) ? '' : " {$key} ";

            
            if(is_array($value[0])){
                foreach ($value as $k => $v) {
                    $where .= " {$key} {$v[0]} {$v[1]} :q_{$v[0]} ";
                    $whereData["q_{$v[0]}"] = $v[2];
                }
            }else{
                $where .= " {$key} {$value[0]} {$value[1]} :q_{$value[0]} ";
                $whereData["q_{$value[0]}"] = $value[2];
            }

        }
        $where = substr($where,0,-1);
        $this->query .= " WHERE {$where} ";

        $this->query .= $this->order;

        if(!is_null($this->limit)){
            $this->query .= " LIMIT {$this->limit}";
        }

        if(!is_null($this->offset)){
            $this->query .= " OFFSET {$this->offset}";
        }

        $this->result = $this->select($this->query,$whereData);

        $this->count = count($this->result);
        $this->query = null;

        return $this;
    }

    public function find(?int $key = null): Datamanager
    {
        $this->query = " SELECT * FROM {$this->table} ";

        if(is_int($key)){
            return $this->findById($key);
        }

        return $this;
    }

}
