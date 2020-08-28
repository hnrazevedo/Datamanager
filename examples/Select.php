<?php

require __DIR__.'/../vendor/autoload.php';
require __DIR__.'/config.php';
require __DIR__.'/Models/User.php';

/* NOTE: in case of error an exception is thrown */

use HnrAzevedo\Datamanager\DatamanagerException;
use Model\User;

try{
    $entity = new User();

    /* Find by primary key */
    $user = $entity->find(1)->execute()->first()->toEntity();

    /* Search only for columns defined in advance  */
    $user = $entity->find(1)->only(['name','email'])->execute()->first();
    $name = $user->name;
    $email = $user->email;
    /* OR */
    $name = $entity->find()->only('name')->execute()->first()->name;

    /* Search except for columns defined in advance  */
    $user = $entity->find()->except(['name','email'])->execute()->first();
    /* OR */
    $user = $entity->find()->except('name')->execute()->first();

    /* Limit example */
    $users = $entity->find()->limit(5)->execute()->result();
    /* Offset example */
    $users = $entity->find()->limit(5)->offset(5)->execute()->result();

    /* OrdeBy example */
    $users = $entity->find()->orderBy('birth ASC')->execute()->result();
    /* OR */
    $users = $entity->find()->orderBy('birth','ASC')->execute()->result();


    /* Between example */
    $user = $entity->find()->between([
        'AND birth'=> [
            '01/01/1996',
            '31/12/1996'
            ]
        ])->execute()->first();

    /* Condition AND is default */
    $user = $entity->find()->between([
        'birth'=> [
            '01/01/1996',
            '31/12/1996'
            ]
        ])->execute()->first();

    /* Clause IN */
    $user = $entity->find()->where([
        'birth'=> [
            '01/01/1996',
            '31/12/1996'
            ]
        ])->execute()->first();


    /* Where example */
    $user->find()->where([
        ['name','=','Henri Azevedo'],
        'OR' => [
            'email','LIKE','otheremail@gmail.com'
            ]
    ])->execute();

    /* Searches through all records and returns a result array */
    $results = $entity->find()->execute()->result();

    /* Searches for all records and returns an array of Model\User objects */
    $results = $entity->find()->execute()->toEntity();

}catch(DatamanagerException $er){

    die("Code Error: {$er->getCode()}, Line: {$er->getLine()}, File: {$er->getFile()}, Message: {$er->getMessage()}.");

}