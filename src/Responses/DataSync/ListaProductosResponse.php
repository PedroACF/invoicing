<?php
namespace PedroACF\Invoicing\Responses\DataSync;

use PedroACF\Invoicing\Responses\BaseResponse;
use Illuminate\Support\Arr;

class ListaProductosResponse extends BaseResponse
{
    public $items = [];

    public static function build($response){
        $data = $response->RespuestaListaProductos ?? (object)[];
        $object = new ListaProductosResponse();
        $object->buildBase($data);
        $items = [];
        foreach ($data->listaCodigos??[] as $item){
            $items[] = new ParamProducto(
                $item->codigoActividad??'',
                $item->codigoProducto??'',
                $item->descripcionProducto??''
            );
        }
        $object->items = $items;
        return $object;
    }
}

class ParamProducto{
    public $codigoActividad = "";
    public $codigoProducto = "";
    public $descripcionProducto = "";

    public function __construct($act, $prod, $desc){
        $this->codigoActividad = $act;
        $this->codigoProducto = $prod;
        $this->descripcionProducto = $desc;
    }
}
