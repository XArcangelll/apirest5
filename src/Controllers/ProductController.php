<?php

namespace App\Api\Controllers;

use App\Api\Config\ResponseHttp;
use App\Api\Config\Security;
use App\Api\Models\ProductModel;

class ProductController{

    private static $validate_stock = "/^[0-9]{1,}$/";
    private static $validate_text = "/^[a-zA-ZÁÉÍÓÚáéíóúñÑ\d\s]+$/";
    private static $validate_description = "/^[a-zA-ZÁÉÍÓÚáéíóúñÑ\d\s]{1,30}$/";

    public function __construct(
        private string $method,
        private string $route,
        private array $params,
        private $data,
        private $headers
    )
    {}

    //registrar producto
    final public function postSave(string $endpoint){
        if ($this->method == "post" && $this->route == $endpoint) {
            Security::validateTokenJwt($this->headers, Security::secretKey());
            if(!self::validateEmpty($this->data) || (!isset($_FILES["product"])) || $_FILES["product"]["size"] == 0  )
            die(json_encode(ResponseHttp::status400("Todos los campos son requeridos incluyendo imagen")));
            self::validateName(self::sanitizeText($this->data["name"]));
            self::validateDescription(self::sanitizeText($this->data["description"]));
            self::validateStock($this->data["stock"]);
          //  echo json_encode($_FILES["product"]["size"]);
            new ProductModel($this->data,$_FILES);
            echo json_encode(ProductModel::postSave());
            exit;
        }
    }

    private static function sanitizeText($text){
        return preg_replace("/\s+/", " ", trim($text));
    }

    private static function validateEmpty($data)
    {
        if ($data == null) die(json_encode("El formato debe ser un json o un buen formdata"));

        $obligacion = array("name", "description", "stock");

        foreach ($obligacion as $valor) {
            if (empty($data[$valor]))
                die(json_encode(ResponseHttp::status400("Todos los campos son requeridos")));
        }
        return true;
    }

    private static function validateName($name)
    {
        if (!preg_match(self::$validate_text, $name))
            die(json_encode(ResponseHttp::status400("El campo admite solo letras y números")));
        return true;
    }

    private static function validateDescription($description)
    {
        if (!preg_match(self::$validate_description, $description))
            die(json_encode(ResponseHttp::status400("El campo admite solo letras y números con un máximo 30 caracteres")));
        return true;
    }

    private static function validateStock($stock)
    {
        if (!preg_match(self::$validate_stock, $stock))
            die(json_encode(ResponseHttp::status400("El stock solo acepta números")));
        return true;
    }





}