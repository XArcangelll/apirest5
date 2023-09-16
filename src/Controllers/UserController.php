<?php

namespace App\Api\Controllers;

use App\Api\Config\ResponseHttp;
use App\Api\Config\Security;
use App\Api\Models\UserModel;

class UserController
{

    private static $validate_rol = '/^[1,2,3]{1,1}$/';
    private static $validate_number = '/^[0-9]+$/';
    private static $validate_text = '/^[a-zA-ZáéíóúÁÉÍÓÚÑñ]+$/';
    //  private static $validate_password = '/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[$@$!%*?&])[A-Za-z\d$@$!%*?&]{8,15}$/'; 
    private static $validate_password = '/^[A-Za-z\d$@$_!%*?&]{8,25}$/';

    public  function __construct(
        private string $method,
        private string $route,
        private array $params,
        private $data,
        private $headers
    ) {
    }

    final public function getLogin(string $endpoint)
    {

        if ($this->method == "get" && $this->route == $endpoint) {

            $email = strtolower($this->params[1]);
            $password = $this->params[2];

            if (empty($email) || empty($password)) die(json_encode(ResponseHttp::status400("Todos los campos son necesarios")));
            self::validateEmail($email);

            UserModel::setCorreo($email);
            UserModel::setPassword($password);
            echo json_encode(UserModel::login());
            exit;
        }
    }


    //OBTENER TODOS LOS USUARIOS

    final public function getAll(string $endpoint)
    {

        if ($this->method == "get" && $this->route == $endpoint) {
            Security::validateTokenJwt($this->headers, Security::secretKey());
            echo json_encode(UserModel::getAll());
            exit;
        }
    }

    //OBTENER UN USUARIO POR DNI

    final public function getUser(string $endpoint)
    {

        if ($this->method == "get" && $this->route == $endpoint) {
            Security::validateTokenJwt($this->headers, Security::secretKey());
            $dni = $this->params[1];
            if (empty($dni)) die(json_encode(ResponseHttp::status400("El campo dni es requerido")));
            self::validateDNI($dni);
            UserModel::setDNI($dni);
            echo json_encode(UserModel::getUser());
            exit;
        }
    }




    final public function post(string $endpoint)
    {
        if ($this->method == 'post' && $this->route == $endpoint) {
           Security::validateTokenJwt($this->headers, Security::secretKey());
            self::validateEmpty($this->data);
            self::validateName($this->data["name"]);
            self::validateDNI($this->data["dni"]);
            self::validateEmail($this->data["email"]);
            self::validateRol($this->data["rol"]);
            self::validatePassword($this->data["password"], $this->data["confirmPassword"]);
            self::validateEqualPassword($this->data["password"], $this->data["confirmPassword"]);
            new userModel($this->data);
            echo json_encode(UserModel::post());
            exit;
        }
    }


    final public function patchPassword(string $endpoint)
    {
        if ($this->method == 'patch' && $this->route == $endpoint) {
            Security::validateTokenJwt($this->headers, Security::secretKey());
            $jwtUserData = Security::getDataJwt();

            if (empty($this->data["oldPassword"]) || empty($this->data["newPassword"]) || empty($this->data["confirmPassword"]))
                die(json_encode(ResponseHttp::status400("Todos los campos son requeridos")));
            if (!UserModel::validateUserPassword($jwtUserData["IDToken"], $this->data["oldPassword"]))
                die(json_encode(ResponseHttp::status400("La contraseña antigua no coincide")));
                self::validatePassword($this->data["newPassword"],$this->data["confirmPassword"]);
                self::validateEqualPassword($this->data["newPassword"], $this->data["confirmPassword"]);
                UserModel::setIDToken($jwtUserData["IDToken"]);
                UserModel::setPassword($this->data["newPassword"]);
                echo json_encode(UserModel::patchPassword());
            exit;
        }
    }

    
    final public function deleteUser(string $endpoint)
    {
        if ($this->method == 'delete' && $this->route == $endpoint) {
            Security::validateTokenJwt($this->headers, Security::secretKey());
          
            if (empty($this->data["IDToken"]))
            die(json_encode(ResponseHttp::status400("Todos los campos son requeridos")));
            UserModel::setIDToken($this->data["IDToken"]);
            echo json_encode(UserModel::deleteUser());
            exit;
        }
    }


    private static function validateEmpty($data)
    {
        if ($data == null) die(json_encode("El formato debe ser un json"));

        $obligacion = array("name", "dni", "email", "rol", "password", "confirmPassword");

        foreach ($obligacion as $valor) {
            if (empty($data[$valor]))
                die(json_encode(ResponseHttp::status400("Todos los campos son requeridos")));
        }

        return true;
    }

    private static function validateName($name)
    {
        if (!preg_match(self::$validate_text, $name))
            die(json_encode(ResponseHttp::status400("El campo solo admite un texto")));
        return true;
    }

    private static function validateDNI($dni)
    {
        if (!preg_match(self::$validate_number, $dni) || (strlen($dni) != 8))
            die(json_encode(ResponseHttp::status400("El campo DNI solo admite numeros y tienen que ser 8 digitos")));
        return true;
    }

    private static function validateEmail($email)
    {
        if (!filter_var($email, FILTER_VALIDATE_EMAIL))
            die(json_encode(ResponseHttp::status400("Formato de correo incorrecto")));
        return true;
    }

    private static function validateRol($rol)
    {
        if (!preg_match(self::$validate_rol, $rol))
            die(json_encode(ResponseHttp::status400("Rol invalido")));
        return true;
    }

    private static function validatePassword($password, $confirmPassword)
    {
        // if(strlen(trim($this->data["password"]," ")) < 8 || strlen(trim($this->data["confirmPassword"]," ")) < 8 )
        if (!preg_match(self::$validate_password, $password) || !preg_match(self::$validate_password, $confirmPassword))
            die(json_encode(ResponseHttp::status400("La contraseña debe tener un minimo de 8 caracteres y máximo de 25")));

        return true;
    }

    private static function validateEqualPassword($password, $confirmPassword)
    {
        if ($password !== $confirmPassword)
            die(json_encode(ResponseHttp::status400("Las contraseñas no coinciden")));
        return true;
    }
}
