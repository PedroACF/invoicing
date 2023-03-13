<?php
namespace PedroACF\Invoicing\Responses\Operation;

use PedroACF\Invoicing\Responses\BaseResponse;
use Illuminate\Support\Arr;

class OperationComunicacionResponse extends BaseResponse
{
    public static function build($response){
        $array = json_decode(json_encode($response), true);
        $resp = Arr::get($array, 'return', []);
        $object = new OperationComunicacionResponse();
        $object->buildBase($resp);
        return $object;
    }
}
