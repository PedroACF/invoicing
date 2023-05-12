<?php
namespace PedroACF\Invoicing\Responses\DataSync;

use PedroACF\Invoicing\Responses\BaseResponse;

class DataSyncComunicacionResponse extends BaseResponse
{
    public static function build($response){
        $data = $response->return ?? (object)[];
        $object = new DataSyncComunicacionResponse();
        $object->buildBase($data);
        return $object;
    }
}
