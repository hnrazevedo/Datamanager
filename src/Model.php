<?php

namespace HnrAzevedo\Datamanager;

use HnrAzevedo\Datamanager\Datamanager;

class Model extends Datamanager
{
    protected array $fields = [];
    protected array $options = [
        'maxlength' => true
    ];

    public function create(string $table, ?string $prikey = null)
    {
        $this->lang();
        parent::synchronize($table, $prikey);
    }
    
    public function getField(string $name)
    {
        return array_key_exists($name, $this->fields) ? $this->fields[$name] : $name;
    }

    protected function maxlength(bool $option): void
    {
        $this->options['maxlength'] = $option;
    }

    protected function clone(?array $clone = null): array
    {
        if(null !== $clone){
            $this->table = $clone['table'];
            $this->primary = $clone['primary_key'];
            $this->fields($clone['fields']);
            $this->select = $clone['select'];
            $this->data = $clone['data'];
            $this->full = true;
        }

        return [
            'table' => $this->table,
            'primary_key' => $this->primary,
            'fields' => $this->fields(),
            'select' => $this->select,
            'data' => $this->data
        ];
    }

    protected function fields(?array $fields = null): array
    {
        if(null !== $fields){
            $this->fields = $fields;
        }
        return $this->fields;
    }

    private function lang(): void
    {
        if(count(self::$DATAMANAGER_LANG) > 0){
            return;
        }

        $this->throwDefined();

        $lang = (isset(DATAMANAGER_CONFIG['lang'])) ? DATAMANAGER_CONFIG['lang'] : 'en';

        require __DIR__ . DIRECTORY_SEPARATOR . 'languages' . DIRECTORY_SEPARATOR . $lang . '.php';
        self::$DATAMANAGER_LANG = $DATAMANAGER_LANG;
    }

}
