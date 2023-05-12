<?php
namespace PedroACF\Invoicing\Responses\PurchaseSale;

use PedroACF\Invoicing\Responses\BaseResponse;
use Illuminate\Support\Arr;

class PurchaseSaleComunicacionResponse extends BaseResponse
{
    public static function build($response){
        $data = $response->return ?? (object)[];
        $object = new PurchaseSaleComunicacionResponse();
        $object->buildBase($data);
        return $object;
    }
}
