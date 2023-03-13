<?php
namespace PedroACF\Invoicing\Responses\PurchaseSale;

use PedroACF\Invoicing\Responses\BaseResponse;
use Illuminate\Support\Arr;

class PurchaseSaleComunicacionResponse extends BaseResponse
{
    public static function build($response){
        $array = json_decode(json_encode($response), true);
        $resp = Arr::get($array, 'return', []);
        $object = new PurchaseSaleComunicacionResponse();
        $object->buildBase($resp);
        return $object;
    }
}
