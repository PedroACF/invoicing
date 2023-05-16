<?php
namespace PedroACF\Invoicing\Responses\Code;
use PedroACF\Invoicing\Responses\BaseResponse;
use Illuminate\Support\Arr;

class VerificarNitResponse extends BaseResponse
{
    public static function build($response){
        $data = $response->RespuestaVerificarNit ?? (object)[];
        $object = new VerificarNitResponse();
        $object->buildBase($data);
        return $object;
    }
}
