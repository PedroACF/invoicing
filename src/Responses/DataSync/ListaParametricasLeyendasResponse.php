<?php
namespace PedroACF\Invoicing\Responses\DataSync;

use PedroACF\Invoicing\Responses\BaseResponse;
use Illuminate\Support\Arr;

class ListaParametricasLeyendasResponse extends BaseResponse
{
    public $items = [];

    public static function build($response){
        $array = json_decode(json_encode($response), true);
        $resp = Arr::get($array, 'RespuestaListaParametricasLeyendas', []);
        $object = new ListaParametricasLeyendasResponse();
        $object->buildBase($resp);

        $items = [];
        foreach (Arr::get($resp, 'listaLeyendas', [] ) as $item){
            $items[] = new ParamLeyenda(
                Arr::get($item, 'codigoActividad', ""),
                Arr::get($item, 'descripcionLeyenda', "")
            );
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
