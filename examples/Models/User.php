<?php

namespace Model;

use HnrAzevedo\Datamanager\Attributes\Entity;
use HnrAzevedo\Datamanager\Attributes\Column;

#[Entity(table: 'User')]
class User
{
    #[Column(name: 'name', type: 'string', max: 50, min: 5)]
    public string $name;
}
