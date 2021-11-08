<?php

namespace Model;

use HnrAzevedo\Datamanager\Model as Entity;
/** 
  * @property string $name 
  * @property string $email 
  * @property string $password
  * @property string birth
  * @property string register
  * @property string $birth
  * @property string $register
  * @property string $weight
  */ 
class User extends Entity
{
    public function __construct()
    {
        $this->fields = [
            'email'=>'Email',
            'username'=>'Nome de usuário'
            'email' => 'Email',
            'username' => 'Nome de usuário',
            'weight' => 'Peso'
        ];
        /**
         * @param string Table name
         * @param string Primary key column
         */
        parent::create('user', 'id');
    }
}
