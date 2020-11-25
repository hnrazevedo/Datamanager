<?php

namespace HnrAzevedo\Datamanager;

trait SynchronizeTrait
{
    use CrudTrait,
        Typing;

    protected ?string $table = null;
    protected ?string $primary = null;
    protected bool $full = false;
    protected static ?array $describe = null;

    protected function synchronize(string $table, ?string $primary = null): self
    {
        $this->table = $table;
        $this->primary = $primary;
        self::$describe[$table] ??= $this->describe();
        
        $this->check_fail();

        $this->mountData(self::$describe[$table]);
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

    protected function mountTable_Field(string $field, $value = null): void
    {
        $this->$field = null;
    }

    protected function mountTable_Type(string $field, $value = null): void
    {
        $this->$field = ['type' => $this->getType($value)];
        $this->mountTable_Maxlength($field, $this->getType($value), $value);
    }

    protected function mountTable_Maxlength(string $field, string $type, $default = null): void
    {
        $this->$field = ['maxlength' => $this->getMax($type, $default)];
    }

    protected function mountTable_Null(string $field, $value = null): void
    {
        $this->$field = ['null' => ($value === 'YES') ? 1 : 0];
    }

    protected function mountTable_Key(string $field, $value = null): void
    {
        $this->$field = ['key' => $value];
        $this->$field = ['upgradeable' => ($value == 'PRI') ? 0 : 1];
    }

    protected function mountTable_Extra(string $field, $value = null): void
    {
        $this->$field = ['extra' => $value];
    }

    protected function mountTable_Default(string $field, $value = null): void
    {
        $this->$field = ['default' => $value];
        $this->$field = ['value' => null];
        $this->$field = ['changed' => false];
        $this->select[$field] = true;
    }

}
