<?php

require __DIR__.'/../vendor/autoload.php';
require __DIR__.'/config.php';
require __DIR__.'/Models/User.php';

/* NOTE: in case of error an exception is thrown */

use HnrAzevedo\Datamanager\DatamanagerException;
use Model\User;

try{
    $entity = new User();

    $user = $entity->find()->execute()->first();

    /* Change info to update */
    $user->name = 'Other Name';
    $user->email = 'otheremail@gmail.com';

    /* Upload by primary key from the uploaded entity */
    /* If the changed information is a primary key or a foreign key it will be ignored in the update */
    /* NOTE: Must already have the Model returned from a query */
    $user->save();

}catch(DatamanagerException $er){

    die("Code Error: {$er->getCode()}, Line: {$er->getLine()}, File: {$er->getFile()}, Message: {$er->getMessage()}.");

}