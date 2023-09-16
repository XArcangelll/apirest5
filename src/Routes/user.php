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


$app->getAll('user/');
$app->getUser("user/{$params[1]}");
$app->post('user/');
$app->patchPassword("user/password/");
$app->deleteUser("user/");



echo json_encode(ResponseHttp::status404());



// $variable = "user////aeaea///////www/sss";
// //echo $variable;

// $aea =rtrim($variable,"/");
// //echo $aea;

// $vergas = str_replace("/"," ",$variable);

// $arreglo = array_unique(explode(" ",$vergas));
// //print_r(array_values($arreglo));

// $nuevoarreglo = array_filter($arreglo,function($valor){
//     return $valor != "";
// });
// print_r(array_values($nuevoarreglo));

