<?php
namespace PedroACF\Invoicing\Responses\PurchaseSale;

use PedroACF\Invoicing\Responses\BaseResponse;
use Illuminate\Support\Arr;
use PedroACF\Invoicing\Responses\Message;

class ServicioFacturacionResponse extends BaseResponse
{
    public $codigoRecepcion = '';
    public $codigoDescripcion = '';
    public $codigoEstado = 0;
    public static function build($response){
        $data = $response->RespuestaServicioFacturacion ?? (object)[];
        $object = new ServicioFacturacionResponse();

        $object->transaccion = $data->transaccion??false;
        $object->codigoRecepcion = $data->codigoRecepcion??'';
        $object->codigoDescripcion = $data->codigoDescripcion??'';
        $object->codigoEstado = $data->codigoEstado??0;
        $object->buildBase($data);
        $object->mensajes[] = new Message(
            $data->codigoEstado??0,
            $data->codigoDescripcion,
            ''
        );
        return $object;
    }
}
