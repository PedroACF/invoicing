<?php
namespace PedroACF\Invoicing\Responses\DataSync;

use PedroACF\Invoicing\Responses\BaseResponse;
use Illuminate\Support\Arr;

class ListaActividadesResponse extends BaseResponse
{
    public $listaActividades = [];

    public static function build($response){
        $array = json_decode(json_encode($response), true);
        $resp = Arr::get($array, 'RespuestaListaActividades', []);
        $object = new ListaActividadesResponse();
        $object->buildBase($resp);

        $activities = [];
        foreach (Arr::get($resp, 'listaActividades', [] ) as $item){
            $activities[] = new Actividad(
                Arr::get($item, 'codigoCaeb', ""),
                Arr::get($item, 'descripcion', ""),
                Arr::get($item, 'tipoActividad', ""),
            );
        }
        $object->listaActividades = $activities;
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
