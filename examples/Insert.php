<?php

require __DIR__.'/../vendor/autoload.php';
require __DIR__.'/config.php';
require __DIR__.'/Models/User.php';

/* NOTE: in case of error an exception is thrown */

/* It is necessary to set a value in all fields that cannot be null, otherwise a DatamanagerException will be thrown */

use HnrAzevedo\Datamanager\DatamanagerException;
use Model\User;

try{
    $entity = new User();

    /* Set new info for insert in database */
    $entity->name = 'Henri Azevedo';
    $entity->email = 'hnr.azevedo@gmail.com';
    $entity->password = password_hash('123456', PASSWORD_DEFAULT);
    $entity->birth = '28/09/1996';
    $entity->register = date('Y-m-d H:i:s');
    $entity->weight = floatval('70.50');

    /* Insert entity in database */
    $entity->persist();

}catch(DatamanagerException $er){

    die("Code Error: {$er->getCode()}, Line: {$er->getLine()}, File: {$er->getFile()}, Message: {$er->getMessage()}.");

}