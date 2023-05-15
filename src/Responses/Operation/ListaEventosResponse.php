<?php
namespace PedroACF\Invoicing\Responses\Operation;

use PedroACF\Invoicing\Responses\BaseResponse;
use Illuminate\Support\Arr;

class ListaEventosResponse extends BaseResponse
{
    public $codigoRecepcionEventoSignificativo = '';
    public static function build($response){
        $data = $response->RespuestaListaEventos ?? (object)[];
        $object = new ListaEventosResponse();
        $object->codigoRecepcionEventoSignificativo = $data->codigoRecepcionEventoSignificativo??null;
        $object->buildBase($data);
        return $object;
    }
}
