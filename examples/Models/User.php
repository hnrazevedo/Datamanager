<?php

namespace Model;

use HnrAzevedo\Datamanager\Datamanager;

class User extends Datamanager{

    public function __construct()
    {
        parent::create('user','id');
    }

}