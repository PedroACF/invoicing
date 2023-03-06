<?php
namespace PedroACF\Invoicing\Responses\Code;
use PedroACF\Invoicing\Responses\BaseResponse;
use Illuminate\Support\Arr;

class CodeComunicacionResponse extends BaseResponse
{
    public static function build($response){
        $array = json_decode(json_encode($response), true);
        $resp = Arr::get($array, 'RespuestaComunicacion', []);
        $object = new CodeComunicacionResponse();
        $object->buildBase($resp);
        return $object;
    }
}
