<?php

namespace HnrAzevedo\Datamanager;

use Exception;

abstract class Datamanager
{
    use CrudTrait;

    private ?string $table = null;
    private ?string $primary = null;
    private array $result = [];
    protected array $data = [];
    
    private bool $full = false;
    private ?string $clause = null;


    private array $where = [''=> ["1",'=',"1"] ];
    private ?string $order = null;
    private ?string $limit = null;
    private ?int $offset = null;
    private array $excepts = [];
    private int $count = 0;
    private array $select = [];
    private ?string $query = null;


    protected function create(string $table, string $primary): Datamanager
    {
        $this->table = $table;
        $this->primary = $primary;
        $describe = $this->describe();
        
        $this->check_fail();

        $this->mountData($describe);
        $this->full = true;
        return $this;
    }

    private function mountTable_Field(string $field, $value = null)
    {
        $this->$field = null;
    }

    private function mountTable_Type(string $field, $value = null)
    {
        $type = $value;
        $maxlength = null;

        if(strpos($value,'(')){
            $type = (array_key_exists( substr($value,0,strpos($value,'(')) , ['varchar','char','text'])) ? 'string' : $type;
            $type = (array_key_exists( substr($value,0,strpos($value,'(')) , ['tinyint','mediumint','smallint','bigtint','int'])) ? 'int' : $type;
            $type = (array_key_exists( substr($value,0,strpos($value,'(')) , ['decimal','float','double','real'])) ? 'float' : $type;
        }

        $maxlength = (array_key_exists( $type , ['string','float','int'])) ? substr($value,(strpos($value,'(')+1),-1) : $maxlength;
        $maxlength = (array_key_exists( $type , ['date'])) ? 10 : $maxlength;
        $maxlength = (array_key_exists( $type , ['datetime'])) ? 19 : $maxlength;
        $maxlength = (array_key_exists( $type , ['boolean'])) ? 1 : $maxlength;

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
        if(!array_key_exists(str_replace(['asc','ASC','desc','DESC',' '],'',$field),$this->data) && $this->full){
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

            if(!array_key_exists($field,$this->data) && $this->full){
                throw new Exception("{$field} field does not exist in the table {$this->table}.");
            }

            $this->select[$field] = true;
        }
        $this->select[$this->primary] = true;

        return $this;
    }

    public function where(array $where): Datamanager
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

    public function check_where_array(array $where)
    {
        if(count($where) != 3){
            throw new Exception("Condition where set incorrectly: ".implode(' ',$where));
        }

        if(!array_key_exists($where[0],$this->data) && $this->full){
            throw new Exception("{$where[0]} field does not exist in the table {$this->table}.");
        }
    }

    public function limit(string $limit): Datamanager
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

    private function mountRemove(): array
    {
        $return = ['data' => [], 'where' => []];
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

    public function remove(?bool $exec = false): Datamanager
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

    public function findById($id): Datamanager
    {
        $this->where([$this->primary,'=',$id]);
        return $this;
    }

    private function mountWhereExec(): array
    {
        $return = ['where' => [], 'data' => []];

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

    public function execute(): Datamanager
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

    public function find(?int $key = null): Datamanager
    {
        $this->query = " SELECT * FROM {$this->table} ";

        if(is_int($key)){
            return $this->findById($key);
        }

        return $this;
    }

}
