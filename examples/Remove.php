<?php

require __DIR__.'/../vendor/autoload.php';
require __DIR__.'/config.php';
require __DIR__.'/Models/User.php';

/* NOTE: in case of error an exception is thrown */

use HnrAzevedo\Datamanager\DatamanagerException;
use Model\User;

try{
    $entity = new User();

    /* Remove by cause *Where* */
    $entity->remove()->where([
        ['name','=','Other Name'],
        'OR' => ['email','LIKE','otheremail@gmail.com']
    ])->execute();

    /* Remove by primary key */
    /* NOTE: Required to have already returned a query */
    $entity->remove()->execute();
    /* OR */
    $entity->remove(true);

}catch(DatamanagerException $er){

    die("Code Error: {$er->getCode()}, Line: {$er->getLine()}, File: {$er->getFile()}, Message: {$er->getMessage()}.");

}