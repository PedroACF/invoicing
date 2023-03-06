<?php
namespace PedroACF\Invoicing\Responses\DataSync;

use PedroACF\Invoicing\Responses\BaseResponse;
use Illuminate\Support\Arr;

class DataSyncComunicacionResponse extends BaseResponse
{
    public static function build($response){
        $array = json_decode(json_encode($response), true);
        $resp = Arr::get($array, 'return', []);
        $object = new DataSyncComunicacionResponse();
        $object->buildBase($resp);
        return $object;
    }
}
