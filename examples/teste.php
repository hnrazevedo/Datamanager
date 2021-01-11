<?php

require __DIR__.'/../vendor/autoload.php';
require __DIR__.'/Models/User.php';

use Model\User;

$user = new User();

function dumpAttributeData($reflection) {
    $attributes = $reflection->getAttributes();

    foreach ($attributes as $attribute) {
       //var_dump($attribute->getName());
       //var_dump($attribute->getArguments());
       //var_dump($attribute->newInstance());
    }

    $properties = $reflection->getProperties();
    foreach ($properties as $property) {
        foreach ($property->getAttributes() as $attribute) {
            var_dump($attribute->getName());
            var_dump($attribute->getArguments());
            var_dump($attribute->newInstance());
        }
     }

}

dumpAttributeData(new ReflectionClass(User::class));