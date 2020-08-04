<?php

require __DIR__.'/../vendor/autoload.php';
require __DIR__.'/Models/User.php';


use Model\User;

$entity = new User();

/* Set new info for insert in database */
$entity->name = 'Henri Azevedo';
$entity->email = 'hnr.azevedo@gmail.com';
$entity->password = '123456';

/* Insert entity in database */
$entity->persist();


/* Find by primary key */
$user = $entity->find(1)->execute()->toEntity();

/* Change info to update */
$user->name = 'Other Name';
$user->email = 'otheremail@gmail.com';

/* Upload by primary key */
$user->save();

/* Remove by primary key */
$user->remove();

var_dump($user->id);


/* NOTE: in case of error an exception is thrown */