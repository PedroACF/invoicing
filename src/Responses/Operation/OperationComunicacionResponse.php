<?php
namespace PedroACF\Invoicing\Responses\Operation;

use PedroACF\Invoicing\Responses\BaseResponse;
use Illuminate\Support\Arr;

class OperationComunicacionResponse extends BaseResponse
{
    public static function build($response){
        $data = $response->return ?? (object)[];
        $object = new OperationComunicacionResponse();
        $object->buildBase($data);
        return $object;
    }
}
