<?php

namespace Model;

use HnrAzevedo\Datamanager\Datamanager;

/** 
  * @property string $name 
  * @property string $email 
  * @property string $password
  * @property string birth
  * @property string register
  */ 
class User extends Datamanager{

    public function __construct()
    {
        /**
         * @param string Table name
         * @param string Primary key column
         */
        parent::create('user','id');
    }

}