<?php

namespace App\Api\Models;

use App\Api\Config\ResponseHttp;
use App\Api\Config\Security;
use App\Api\DB\ConnectionDB;
use App\Api\DB\Sql;
use PDO;
use PDOException;

class UserModel extends ConnectionDB
{

    private static string $nombre;
    private static string $dni;
    private static string $correo;
    private static int $rol;
    private static string $password;
    private static string $IDToken;
   // private static string $fecha;

    public function __construct(array $data)
    {
        self::$nombre = $data["name"];
        self::$dni = $data["dni"];
        self::$correo = $data["email"];
        self::$rol = $data["rol"];
        self::$password = $data["password"];
    }


    //login

    final public static function login(){
        try{
            $con = self::getConnection();
            $query = $con->prepare("SELECT * FROM usuario where correo = :correo");
            $query->execute([
                ":correo"=>self::getCorreo()
            ]);

            $data = $query->fetch(PDO::FETCH_ASSOC);
            if($query->rowCount() == 0) return ResponseHttp::status200("El usuario y/o contraseña incorrectos");
            if(!Security::validatePassword(self::getPassword(),$data["password"])) return ResponseHttp::status200("El usuario y/o contraseña incorrectos");

            $payload = ["IDToken"=> $data["IDToken"]];
            $token = Security::createTokenJwt(Security::secretKey(),$payload);

            $dataJson = [
                "name" => $data["nombre"],
                "rol" => $data["rol"],
                "token" => $token
            ];

            return ResponseHttp::status200($dataJson);

        }catch(PDOException $e){
            error_log("UserModel::login -> " .$e);
            die(json_encode(ResponseHttp::status500()));     
        }
    }

    //OBTENER TODOS LOS USUARIOS

    final public static function getAll(){
            try {
                    $con = self::getConnection();
                    $query = $con->prepare("SELECT * FROM usuario");
                    $query->execute();
                    $rs["data"] = $query->fetchAll(PDO::FETCH_ASSOC);
                    if($query->rowCount() == 0)  return ResponseHttp::status400("No hay usuarios registrados");
                    return $rs;
            } catch (PDOException $e) {
                error_log("UserModel::getAll -> " .$e);
                die(json_encode(ResponseHttp::status500("No se puede obtener los datos")));     
            }
    }

       //OBTENER usuario;

    final public static function getUser(){
        try {
                $con = self::getConnection();
                $query = $con->prepare("SELECT * FROM usuario where dni= :dni");
                $query->execute([
                    ":dni"=>self::getDNI()
                ]);
                $rs["data"] = $query->fetch(PDO::FETCH_ASSOC);
                if($query->rowCount() == 0)  return ResponseHttp::status400("No hay usuario con este dni");
                return $rs;
        } catch (PDOException $e) {
            error_log("UserModel::getUser -> " .$e);
            die(json_encode(ResponseHttp::status500("No se puede obtener los datos del usuario")));     
        }
}

    //registrar usuario

    final public static function post()
    {
        if (Sql::exists("SELECT dni FROM usuario WHERE dni = :dni", ":dni", self::getDNI()))
            return ResponseHttp::status400("El DNI ya está registrado");
        if (Sql::exists("SELECT correo FROM usuario WHERE correo = :correo", ":correo", self::getCorreo()))
            return ResponseHttp::status400("El correo ya está registrado");

        self::setIDToken(hash('sha512',self::getDNI().self::getCorreo()));
      //  self::setFecha(date("Y-m-d H:i:s"));

        try {
            $con = self::getConnection();
            $query1 = "INSERT INTO usuario(nombre,dni,correo,rol,password,IDToken) VALUES ";
            $query2 = "(:nombre,:dni,:correo,:rol,:password,:IDToken)";
            $query = $con->prepare($query1.$query2);
            $query->execute([
                ":nombre"=>self::getName(),
                ":dni"=>self::getDNI(),
                ":correo"=>self::getCorreo(),
                ":rol"=>self::getRol(),
                ":password"=>Security::createPassword(self::getPassword()),
                ":IDToken"=>self::getIDToken()
              //  ":fecha"=>self::getFecha()
            ]);

            if($query->rowCount()) return ResponseHttp::status200("Usuario registrado correctamente");
            return ResponseHttp::status500("No se pudo registrar el usuario");

        } catch (PDOException $e) {
                error_log("UserModel::post -> " .$e);
                die(json_encode(ResponseHttp::status500()));     
        }

    }


    //validacion contraseña antigua correcta

    final public static function validateUserPassword(string $IDToken, string $oldPassword){
        try {
           $con = self::getConnection();
           $query = $con->prepare("SELECT password FROM usuario WHERE IDToken = :IDToken");
           $query->execute([
            ":IDToken" => $IDToken
           ]);

           if($query->rowCount() == 0) die(json_encode(ResponseHttp::status500("Hubo un problema con el IDToken")));
           $data = $query->fetch(PDO::FETCH_ASSOC);

           return Security::validatePassword($oldPassword,$data["password"]);

        } catch (\PDOException $e) {
            error_log("UserModel::validateUserPassword -> " .$e);
            die(json_encode(ResponseHttp::status500()));   
        }
    }

    //actualizar la contraseña del usuario
    final public static function patchPassword()
    {
        try {
            $con = self::getConnection();
            $query = $con->prepare("UPDATE usuario SET password = :password WHERE IDToken = :IDToken");
            $query->execute([
                ":password"=> Security::createPassword(self::getPassword()),
                ":IDToken"=> self::getIDToken()
            ]);

            if($query->rowCount() > 0) return ResponseHttp::status200("Contraseña actualizada exitosamente");
            // puede que a veces el rowcount no de mayor a  0 porq no hubo cambios en un set no necesariamente salio algo mal
            return ResponseHttp::status500("Error al actualizar la contraseña del usuario");

        } catch (\PDOException $e) {
            error_log("UserModel::patchPassword -> " .$e);
            die(json_encode(ResponseHttp::status500())); 
        }
    }

     //borrar un usuario 
     final public static function deleteUser()
     {
         try {
             $con = self::getConnection();
             $query = $con->prepare("DELETE FROM usuario WHERE IDToken = :IDToken");
             $query->execute([
                 ":IDToken"=> self::getIDToken()
             ]);
 
             if($query->rowCount() > 0) return ResponseHttp::status200("Usuario eliminado exitosamente");
             // puede que a veces el rowcount no de mayor a  0 porq no hubo cambios en un set no necesariamente salio algo mal
             return ResponseHttp::status500("Error al eliminar al usuario");
 
         } catch (\PDOException $e) {
             error_log("UserModel::deleteUser -> " .$e);
             die(json_encode(ResponseHttp::status500())); 
         }
     }

    final public static function getName()
    {
        return self::$nombre;
    }
    final public static function getDNI()
    {
        return self::$dni;
    }
    final public static function getCorreo()
    {
        return self::$correo;
    }
    final public static function getRol()
    {
        return self::$rol;
    }
    final public static function getPassword()
    {
        return self::$password;
    }
    final public static function getIDToken()
    {
        return self::$IDToken;
    }
    // final public static function getFecha()
    // {
    //     return self::$fecha;
    // }

    final public static function setName(string $nombre)
    {
        self::$nombre = $nombre;
    }
    final public static function setDNI(string $dni)
    {
        self::$dni = $dni;
    }
    final public static function setCorreo(string $correo)
    {
        self::$correo = $correo;
    }
    final public static function setRol(int $rol)
    {
        self::$rol = $rol;
    }
    final public static function setPassword(string $password)
    {
        self::$password = $password;
    }
    final public static function setIDToken(string $IDToken)
    {
        self::$IDToken = $IDToken;
    }
    // final public static function setFecha(string $fecha)
    // {
    //     self::$fecha = $fecha;
    // }

    
}
