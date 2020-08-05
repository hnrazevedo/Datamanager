<?php

namespace HnrAzevedo\Datamanager;

use PDO;
use PDOException;

class Connect
{
    private static $instance;

    public static function getInstance(): ?PDO
    {
        if (empty(self::$instance)) {
            try {
                self::$instance = new PDO(
                    DATAMANAGER_CONFIG['driver'] . ':host='.DATAMANAGER_CONFIG['host'] . ';port='.DATAMANAGER_CONFIG['port'] . ';dbname='.DATAMANAGER_CONFIG['database'] . ';charset='.DATAMANAGER_CONFIG['charset'],
                    DATAMANAGER_CONFIG['username'],
                    DATAMANAGER_CONFIG['password'],
                    DATAMANAGER_CONFIG['options']
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
