<?php

use App\Api\Config\ErrorLog;
use App\Api\Config\ResponseHttp;
use App\Api\Config\Security;

require 'vendor/autoload.php';


ResponseHttp::headerHttpDev($_SERVER["REQUEST_METHOD"]);

ErrorLog::activateErrorLog();

if(isset($_GET['route'])){
   
    $url = explode('/',$_GET["route"]);
    $lista = ['auth','user','product'];
  //  $file = dirname(__DIR__) . "/src/Routes/" .$url[0] . '.php';
  $file = "src/Routes/" .$url[0] . '.php';

    if(!in_array($url[0],$lista)){
        echo json_encode(ResponseHttp::status400());
        error_log('esto es una prueba de error');
       // Security::secretKey();
        exit;
    }

    if(is_readable($file)){
        require $file;
        exit;
    }else{
        echo json_encode(ResponseHttp::status400());
    }

}else{
    echo json_encode(ResponseHttp::status404());
}