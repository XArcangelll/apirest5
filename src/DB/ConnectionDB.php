<?php

namespace App\Api\DB;

use App\Api\Config\ResponseHttp;
use PDO;
use PDOException;

require "DataDB.php";

class ConnectionDB
{

    private static $host = '';
    private static $user = '';
    private static $password = '';

    final public static function from($host, $user, $password)
    {
        self::$host = $host;
        self::$user = $user;
        self::$password = $password;
    }

    final public static function getConnection(){

        try {
            $opt = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ];
            $dsn = new PDO(self::$host,self::$user,self::$password,$opt);
            error_log('Conexión exitosa');
            return $dsn;
        } catch (PDOException $p) {
            error_log('Error de conexión ' .$p);
            die(json_encode(ResponseHttp::status500()));
        }
    }

}
