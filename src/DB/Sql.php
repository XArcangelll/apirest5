<?php

namespace App\Api\DB;

use App\Api\Config\ResponseHttp;
use PDOException;

class Sql extends ConnectionDB{

    public static function exists(string $request, string $condition, $param){
        try {
            $con = self::getConnection();
            $query = $con->prepare($request);
            $query->execute([
                $condition => $param
            ]);

            return ($query->rowCount() > 0);
        } catch (PDOException $e) {
           error_log('Sql::exists -> ' .$e);
           die(json_encode(ResponseHttp::status500()));
        }
    }

}