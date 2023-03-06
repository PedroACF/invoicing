<?php
namespace PedroACF\Invoicing\Responses;

class Mensaje
{
    public $codigo = 0;
    public $descripcion = "";

    public function __construct($codigo, $descripcion)
    {
        $this->codigo = $codigo;
        $this->descripcion = $descripcion;
    }
}
