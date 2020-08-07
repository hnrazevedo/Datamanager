<?php

require __DIR__.'/../vendor/autoload.php';
require __DIR__.'/config.php';
require __DIR__.'/Models/User.php';

/* NOTE: in case of error an exception is thrown */

use Model\User;

$entity = new User();

try{
    /* Set new info for insert in database */
    $entity->name = 'Henri Azevedo';
    $entity->email = 'hnr.azevedo@gmail.com';
    $entity->password = '123456';

    /* Insert entity in database */
    $entity->persist();

    /* Find by primary key */
    $user = $entity->find()->execute()->first()->toEntity();

    /* Search only for columns defined in advance  */
    $user = $entity->find()->only(['name','email'])->execute()->first();
    $name = $user->name;
    $email = $user->email;

    /* OR */
    $name = $entity->find()->only('name')->execute()->first()->name;

    /* Change info to update */
    $user->name = 'Other Name';
    $user->email = 'otheremail@gmail.com';

    /* Upload by primary key from the uploaded entity */
    $user->save();
    /* Remove by primary key from the uploaded entity */
    $user->remove(true);
    /* OR */
    $user->remove()->execute();

    /* Remove by cause *Where* */
    $user->remove()->where([
        ['name','=','Other Name'],
        'OR' => ['email','LIKE','otheremail@gmail.com']
    ])->execute();
}catch(Exception $er){

    die("Code Error: {$er->getCode()}, Line: {$er->getLine()}, File: {$er->getFile()}, Message: {$er->getMessage()}.");

}




