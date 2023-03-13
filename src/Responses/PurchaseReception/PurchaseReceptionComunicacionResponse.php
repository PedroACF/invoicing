<?php
namespace PedroACF\Invoicing\Responses\PurchaseReception;

use PedroACF\Invoicing\Responses\BaseResponse;
use Illuminate\Support\Arr;

class PurchaseReceptionComunicacionResponse extends BaseResponse
{
    public static function build($response){
        $array = json_decode(json_encode($response), true);
        $resp = Arr::get($array, 'return', []);
        $object = new PurchaseReceptionComunicacionResponse();
        $object->buildBase($resp);
        return $object;
    }
}
