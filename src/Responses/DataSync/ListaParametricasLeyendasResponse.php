<?php
namespace PedroACF\Invoicing\Responses\DataSync;

use PedroACF\Invoicing\Responses\BaseResponse;
use Illuminate\Support\Arr;

class ListaParametricasLeyendasResponse extends BaseResponse
{
    public $items = [];

    public static function build($response){
        $data = $response->RespuestaListaParametricasLeyendas ?? (object)[];
        $object = new ListaParametricasLeyendasResponse();
        $object->buildBase($data);
        $items = [];
        foreach ($data->listaLeyendas ?? [] as $item){
            $items[] = new ParamLeyenda($item->codigoActividad??'', $item->descripcionLeyenda ?? '');
        }
        $object->items = $items;
        return $object;
    }
}

class ParamLeyenda{
    public $codigoActividad = "";
    public $descripcionLeyenda = "";

    public function __construct($codAct, $descLeyenda){
        $this->codigoActividad = $codAct;
        $this->descripcionLeyenda = $descLeyenda;
    }
}
