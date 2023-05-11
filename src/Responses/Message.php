<?php
namespace PedroACF\Invoicing\Responses;

class Message
{
    public $code = 0;
    public $description = "";
    public $type = "";

    public function __construct($codigo, $descripcion, $tipo)
    {
        $this->code = $codigo;
        $this->description = $descripcion;
        $this->type = $tipo;
    }
}
