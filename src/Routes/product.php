<?php

use App\Api\Config\ResponseHttp;
use App\Api\Controllers\ProductController;

$method = strtolower($_SERVER["REQUEST_METHOD"]);
$route = $_GET["route"];
$url = rtrim($route, "/");
$params = explode('/',$route);
$data = (empty($_POST)) ? json_decode(file_get_contents("php://input"),true) : $_POST;
$headers = getallheaders();


$app = new ProductController($method,$route,$params,$data,$headers);

$app->postSave("product/");


echo json_encode(ResponseHttp::status404());
