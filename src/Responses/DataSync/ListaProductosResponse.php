<?php
namespace PedroACF\Invoicing\Responses\DataSync;

use PedroACF\Invoicing\Responses\BaseResponse;
use Illuminate\Support\Arr;

class ListaProductosResponse extends BaseResponse
{
    public $items = [];

    public static function build($response){
        $array = json_decode(json_encode($response), true);
        $resp = Arr::get($array, 'RespuestaListaProductos', []);
        $object = new ListaProductosResponse();
        $object->buildBase($resp);

        $items = [];
        foreach (Arr::get($resp, 'listaCodigos', [] ) as $item){
            $items[] = new ParamProducto(
                Arr::get($item, 'codigoActividad', ""),
                Arr::get($item, 'codigoProducto', ""),
                Arr::get($item, 'descripcionProducto', "")
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
