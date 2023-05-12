<?php
namespace PedroACF\Invoicing\Responses\Code;
use PedroACF\Invoicing\Responses\BaseResponse;
use Illuminate\Support\Arr;

class CodeComunicacionResponse extends BaseResponse
{
    public static function build($response){
        $data = $response->RespuestaComunicacion ?? (object)[];
        $object = new CodeComunicacionResponse();
        $object->buildBase($data);
        return $object;
    }
}
