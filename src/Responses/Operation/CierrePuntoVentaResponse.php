<?php
namespace PedroACF\Invoicing\Responses\Operation;

use PedroACF\Invoicing\Responses\BaseResponse;
use Illuminate\Support\Arr;

class CierrePuntoVentaResponse extends BaseResponse
{
    public $codigoPuntoVenta = -1;

    public static function build($response){
        $data = $response->RespuestaCierrePuntoVenta ?? (object)[];
        $object = new CierrePuntoVentaResponse();
        $object->codigoPuntoVenta = $data->codigoPuntoVenta??-1;
        $object->buildBase($data);
        return $object;
    }
}
