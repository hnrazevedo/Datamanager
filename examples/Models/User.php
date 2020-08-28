<?php

namespace Model;

use HnrAzevedo\Datamanager\Model;

/** 
  * @property string $name 
  * @property string $email 
  * @property string $password
  * @property string birth
  * @property string register
  */ 
class User extends Model{

    public function __construct()
    {
        $this->fields = [
            'email'=>'Email',
            'username'=>'Nome de usuário'
        ];
        /**
         * @param string Table name
         * @param string Primary key column
         */
        parent::create('user','id');
    }

}