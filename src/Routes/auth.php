<?php

use App\Api\Config\ResponseHttp;
use App\Api\Controllers\UserController;

$method = strtolower($_SERVER["REQUEST_METHOD"]);
$route = $_GET["route"];
$url = rtrim($route, "/");
$params = explode('/',$route);
$data = json_decode(file_get_contents("php://input"),true);
$headers = getallheaders();

$app = new UserController($method,$route,$params,$data,$headers);

$app->getLogin("auth/{$params[1]}/{$params[2]}");

echo json_encode(ResponseHttp::status404());





// use App\Api\Config\Security;
// use App\Api\DB\ConnectionDB;

// ConnectionDB::getConnection();

// echo json_encode(Security::createTokenJwt(Security::secretKey(),["hola"]));




// $variable = Security::createTokenJwt(Security::secretKey(),["hola"]);
// echo json_encode($variable);
 //echo   json_encode(Security::validateTokenJwt(["Authorization"=>"Bearer $variable"],Security::secretKey()));
// echo json_encode(Security::getDataJwt());

// control k y c para comentar

// $pass = Security::createPassword("prueba");

// echo Security::validatePassword('prueba',$pass) ? json_encode("todo bien") : json_encode("algo salio mal");

//echo json_encode(Security::createPassword("Hola a todos"));