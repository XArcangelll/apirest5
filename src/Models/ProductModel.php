<?php

namespace App\Api\Models;

use App\Api\Config\ResponseHttp;
use App\Api\Config\Security;
use App\Api\DB\ConnectionDB;
use App\Api\DB\Sql;
use PDOException;

class ProductModel extends ConnectionDB{

    private static string $name;
    private static string $description;
    private static int $stock;
    private static $file;
    private static string $url;
    private static string $imageName;
    private static string $IDToken;

    public function __construct(
        array $data, $file
    )
    {
        self::$name = self::sanitizeText($data["name"]); 
        self::$description = self::sanitizeText($data["description"]);   
        self::$stock = $data["stock"];   
        self::$file = $file; 
    }

    private static function sanitizeText($text){
        return preg_replace("/\s+/", " ", trim($text));
    }

    final public static function postSave()
    {
        if (Sql::exists("SELECT name FROM productos WHERE name = :name", ":name", self::getName()))
        return ResponseHttp::status400("El nombre ya está registrado");
        try {

            $resImg = Security::uploadImage(self::getFile(),'product');
            self::setUrl($resImg["path"]);
            self::setImageName($resImg["name"]);
            self::setIDToken(hash('md5',self::getName().self::getUrl())); 

            $con = self::getConnection();
            $query1 = "INSERT INTO productos(name,description,stock,url,imageName,IDToken) VALUES ";
            $query2 = "(:name,:description,:stock,:url,:imageName,:IDToken)";
            $query = $con->prepare($query1.$query2);
            $query->execute([
                ":name"=>self::getName(),
                ":description"=>self::getDescription(),
                ":stock"=>self::getStock(),
                ":url"=>self::getUrl(),
                ":imageName"=>self::getImageName(),
                ":IDToken"=>self::getIDToken()
            ]);

            if($query->rowCount()) return ResponseHttp::status200("Producto registrado correctamente");
            return ResponseHttp::status500("No se pudo registrar el producto");

        } catch (PDOException $e) {
                error_log("ProductModel::postSave -> " .$e);
                die(json_encode(ResponseHttp::status500("Hubo un problema al registrar el producto")));     
        }

    }

    final public static function getName(){ return self::$name;}
    final public static function getDescription(){ return self::$description;}
    final public static function getStock(){ return self::$stock;}
    final public static function getFile(){ return self::$file;}
    final public static function getUrl(){ return self::$url;}
    final public static function getImageName(){ return self::$imageName;}
    final public static function getIDToken(){ return self::$IDToken;}

    final public static function setName(string $name){  self::$name = $name;}
    final public static function setDescription(string $description){  self::$description = $description;}
    final public static function setStock(int $stock){  self::$stock = $stock;}
    final public static function setFile($file){ self::$file = $file;}
    final public static function setUrl(string $url){  self::$url = $url;}
    final public static function setImageName(string $imageName){  self::$imageName = $imageName;}
    final public static function setIDToken(string $IDToken){  self::$IDToken = $IDToken;}


}