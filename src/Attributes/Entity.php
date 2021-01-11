<?php

namespace HnrAzevedo\Datamanager\Attributes;

use Attribute;

#[Attribute]
class Entity
{
    private string $table;

    public function __construct(string $table)
    {
        $this->table = $table;
    }

}
