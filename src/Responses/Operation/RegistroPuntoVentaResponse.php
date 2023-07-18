<?php
namespace PedroACF\Invoicing\Responses\Operation;

use PedroACF\Invoicing\Responses\BaseResponse;
use Illuminate\Support\Arr;

class RegistroPuntoVentaResponse extends BaseResponse
{
    public $codigoPuntoVenta = -1;

    public static function build($response){
        $data = $response->RespuestaRegistroPuntoVenta ?? (object)[];
        $object = new RegistroPuntoVentaResponse();
        $object->codigoPuntoVenta = $data->codigoPuntoVenta??-1;
        $object->buildBase($data);
        return $object;
    }
}
