<?php
namespace PedroACF\Invoicing\Responses\DataSync;

use PedroACF\Invoicing\Responses\BaseResponse;
use Illuminate\Support\Arr;

class ListaActividadesResponse extends BaseResponse
{
    public $items = [];

    public static function build($response){
        $data = $response->RespuestaListaActividades ?? (object)[];
        $object = new ListaActividadesResponse();
        $object->buildBase($data);

        $items = [];
        foreach ($data->listaActividades?? [] as $item){
            $items[] = new Actividad($item->codigoCaeb??'', $item->descripcion??'', $item->tipoActividad??'');
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
