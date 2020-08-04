<?php

namespace HnrAzevedo\Datamanager;

use PDO;
use PDOException;

class Connect
{
    use Config;

    private static $instance;

    public static function getInstance(): ?PDO
    {
        if (empty(self::$instance)) {
            try {
                
                $config = (new self)->config;

                self::$instance = new PDO(
                    "{$config['driver']}:host={$config['host']};port={$config['port']};dbname={$config['database']};charset={$config['charset']}",
                    $config['username'],$config['password'],$config['options']
                );

            } catch (PDOException $exception) {
                throw $exception;
            }
        }
        return self::$instance;
    }

    public static function destroy(){
        self::$instance = null;
    }

}
