<?php
namespace PedroACF\Invoicing\Responses\Operation;

use Carbon\Carbon;
use PedroACF\Invoicing\Responses\BaseResponse;
use Illuminate\Support\Arr;

class ListaEventosResponse extends BaseResponse
{
    public $codigoRecepcionEventoSignificativo = '';
    public $events = [];
    public static function build($response){
        $data = $response->RespuestaListaEventos ?? (object)[];
        $object = new ListaEventosResponse();
        foreach ($data->listaCodigos??[] as $event){
            $remote = RemoteSignificantEvent::build($event);
            $object->events[] = $remote;
        }
        $object->codigoRecepcionEventoSignificativo = $data->codigoRecepcionEventoSignificativo?? null;
        $object->buildBase($data);
        return $object;
    }
}

class RemoteSignificantEvent{
    public $codigoEvento;
    public $codigoRecepcionEventoSignificativo;
    public $descripcion;
    public $fechaFin;
    public $fechaInicio;

    public static function build($data): RemoteSignificantEvent{
        $object = new RemoteSignificantEvent();
        $object->codigoEvento = $data->codigoEvento??0;
        $object->codigoRecepcionEventoSignificativo = $data->codigoRecepcionEventoSignificativo??'';
        $object->descripcion = $data->descripcion?? '';
        $object->fechaFin = $data->fechaFin? new Carbon($data->fechaFin): null;
        $object->fechaInicio = $data->fechaInicio? new Carbon($data->fechaInicio): null;
        return $object;
    }
}
