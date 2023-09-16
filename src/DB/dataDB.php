<?php

namespace App\Api\DB;

use App\Api\DB\ConnectionDB;
use Dotenv\Dotenv;

$dotenv = Dotenv::createImmutable(dirname(__DIR__,2));
$dotenv->load();

$data = array(
    "user" => $_ENV["USER"],
    "password" => $_ENV["PASSWORD"],
    "DB" => $_ENV["DB"],
    "IP" => $_ENV["IP"],
    "port" => $_ENV["PORT"],
    "charset"=>$_ENV["CHARSET"]
);

$host = "mysql:host=".$data["IP"].';'.'port='.$data["port"].';'.'dbname='.$data["DB"].";charset=".$data["charset"];

ConnectionDB::from($host,$data["user"],$data["password"]);
