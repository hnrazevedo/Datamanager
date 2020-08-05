<?php

namespace HnrAzevedo\Datamanager;

use PDO;
use Exception;

class Connect
{
    private static $instance;

    public static function getInstance(): ?PDO
    {
        if (empty(self::$instance)) {
            try {

                if(!defined('DATAMANAGER_CONFIG')){
                    throw new Exception("Information for connection to the database not defined.");
                }

                self::$instance = new PDO(
                    DATAMANAGER_CONFIG['driver'] . ':host='.DATAMANAGER_CONFIG['host'] . ';port='.DATAMANAGER_CONFIG['port'] . ';dbname='.DATAMANAGER_CONFIG['database'] . ';charset='.DATAMANAGER_CONFIG['charset'],
                    DATAMANAGER_CONFIG['username'],
                    DATAMANAGER_CONFIG['password'],
                    DATAMANAGER_CONFIG['options']
                );
            } catch (Exception $exception) {
                throw $exception;
            }
        }
        return self::$instance;
    }

    public static function destroy(){
        self::$instance = null;
    }

}
