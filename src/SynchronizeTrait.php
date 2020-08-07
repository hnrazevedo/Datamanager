<?php

namespace HnrAzevedo\Datamanager;

trait SynchronizeTrait{
    use CrudTrait;

    protected ?string $table = null;
    protected ?string $primary = null;
    protected bool $full = false;

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

    protected function mountData(array $table): Datamanager
    {
        foreach ($table as $column) {
            foreach ($column as $propriety => $value) {
                $method = "mountTable_{$propriety}";
                $this->$method($column['Field'], $value);
            }
        }
        return $this;
    }

    protected function mountTable_Field(string $field, $value = null)
    {
        $this->$field = null;
    }

    protected function mountTable_Type(string $field, $value = null)
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

    protected function mountTable_Null(string $field, $value = null)
    {
        $this->$field = ['null' => ($value === 'YES') ? 1 : 0];
    }

    protected function mountTable_Key(string $field, $value = null)
    {
        $this->$field = ['key' => $value];
        $this->$field = ['upgradeable' => ($value == 'PRI') ? 0 : 1];
    }

    protected function mountTable_Extra(string $field, $value = null)
    {
        $this->$field = ['extra' => $value];
    }

    protected function mountTable_Default(string $field, $value = null)
    {
        $this->$field = ['default' => $value];
        $this->$field = ['value' => null];
        $this->$field = ['changed' => false];
        $this->select[$field] = true;
    }

}