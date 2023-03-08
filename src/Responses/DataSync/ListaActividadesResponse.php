<?php
namespace PedroACF\Invoicing\Responses\DataSync;

use PedroACF\Invoicing\Responses\BaseResponse;
use Illuminate\Support\Arr;

class ListaActividadesResponse extends BaseResponse
{
    public $items = [];

    public static function build($response){
        $array = json_decode(json_encode($response), true);
        $resp = Arr::get($array, 'RespuestaListaActividades', []);
        $object = new ListaActividadesResponse();
        $object->buildBase($resp);

        $items = [];
        foreach (Arr::get($resp, 'listaActividades', [] ) as $item){
            $items[] = new Actividad(
                Arr::get($item, 'codigoCaeb', ""),
                Arr::get($item, 'descripcion', ""),
                Arr::get($item, 'tipoActividad', ""),
            );
        }
        $object->items = $items;
        return $object;
    }
}

class Actividad{
    public $codigoCaeb = "";
    public $descripcion = "";
    public $tipoActividad = "";

    public function __construct($codigoCaeb, $descripcion, $tipoActividad){
        $this->codigoCaeb = $codigoCaeb;
        $this->descripcion = $descripcion;
        $this->tipoActividad = $tipoActividad;
    }
}
