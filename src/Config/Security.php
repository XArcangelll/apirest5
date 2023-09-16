<?php

namespace App\Api\Config;

use Dotenv\Dotenv;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Bulletproof\Image;

class Security{


    private static $jwt_data;

    final public static function secretKey(){
        //pongo el 2 en el dir para q busque en la raiz del proyecto, es decir no en src. esta subiendo un nivel.
        $dotenv = Dotenv::createImmutable(dirname(__DIR__,2));
        $dotenv->load();
        return $_ENV['SECRET_KEY'];
    }

    final public static function createPassword(string $pw){
        $pass = password_hash($pw,PASSWORD_DEFAULT,['cost' => 10]);
        return $pass;
    }

    final public static function validatePassword(string $pw,string $pwhash){
        return password_verify($pw,$pwhash);
    }

    final public static function createTokenJwt(string $key, array $data){
        $payload = array(
            "iat" => time(),
            "exp" => time() + (60*60),
            "data" => $data
        );

        $jwt = JWT::encode($payload,$key,"HS256");
        return $jwt;
    }

    final public static function validateTokenJwt(array $token, string $key){

        if(!isset($token["Authorization"])){
                die(json_encode(ResponseHttp::status400("El token de acceso es requerido")));
                exit;
        }
        try {
            $jwt = explode(" ",$token["Authorization"]);
            $data = JWT::decode($jwt[1],new Key($key, 'HS256'));
            self::$jwt_data = $data;
            return $data;
        } catch (\Exception $th) {
            error_log("Security::validaToken ->  expiro o es invalido");
            die(json_encode(ResponseHttp::status401("Token inválido o expirado")));
        }
    }

    final public static function getDataJwt(){
        $jwt_decoded_array = json_decode(json_encode(self::$jwt_data),true);
        return $jwt_decoded_array["data"] ?? ["No hay data en el jwtdata"];
    }

    //subir imagen al servidor

    final public static function uploadImage($file,$name){

        //trabaja en la version 4.0 la 5.0 da problemas con el getname sale null 
        $file = new Image($file);
        $file->setMime(array('png','jpg','jpeg'));
        $file->setSize(10000,5000000);
        $file->setDimension(2000,3000);
        $file->setLocation("public/Images");

        if($file[$name]){
            $upload = $file->upload();
            if($upload){
                $imgUrl = UrlBase::urlBase .'/public/Images/'. $upload->getName().'.'.$upload->getMime();
                $data = [
                    "path" => $imgUrl,
                    "name" => $upload->getName()
                ];
                return $data;
            }else{
                die(json_encode(ResponseHttp::status400($file->getError())));
            }
        }

    }




}