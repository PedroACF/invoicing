<?php
namespace PedroACF\Invoicing\Responses\DataSync;

use PedroACF\Invoicing\Responses\BaseResponse;
use Illuminate\Support\Arr;

class ListaParametricasResponse extends BaseResponse
{
    public $items = [];

    public static function build($response){
        $array = json_decode(json_encode($response), true);
        $resp = Arr::get($array, 'RespuestaListaParametricas', []);
        $object = new ListaParametricasResponse();
        $object->buildBase($resp);

        $items = [];
        foreach (Arr::get($resp, 'listaCodigos', [] ) as $item){
            $items[] = new ParamCodigo(
                Arr::get($item, 'codigoClasificador', ""),
                Arr::get($item, 'descripcion', "")
            );
        }
        $object->items = $items;
        return $object;
    }
}

class ParamCodigo{
    public $codigoClasificador = "";
    public $descripcion = "";

    public function __construct($cod, $desc){
        $this->codigoClasificador = $cod;
        $this->descripcion = $desc;
    }
}
