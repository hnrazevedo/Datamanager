<?php

namespace HnrAzevedo\Datamanager;

class Model extends Datamanager
{
    public function create(string $table, string $prikey)
    {
        parent::synchronize($table,$prikey);
    }
}