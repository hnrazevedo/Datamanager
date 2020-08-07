<?php

namespace HnrAzevedo\Datamanager;

use HnrAzevedo\Datamanager\DatamanagerException;

trait EntityTrait{
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

    public function persist()
    {
        $columns = '';
        $values = '';
        $data = [];

        foreach ($this->data as $key => $value) {
            if(strstr($this->data[$key]['extra'],'auto_increment')){
                continue;
            }

            $this->checkMaxlength($key, $value['value'], $value['maxlength']);

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

        }catch(DatamanagerException $er){
            $this->transaction('rollback');
            throw $er;
        }

        return $this;
    }

    public function remove(?bool $exec = false)
    {
        if($exec !== null){
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

    public function save()
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
        }catch(DatamanagerException $er){
            $this->transaction('rollback');
            throw $er;
        }

        return $this;
    }
}