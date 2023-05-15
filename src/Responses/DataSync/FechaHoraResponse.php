<?php
namespace PedroACF\Invoicing\Responses\DataSync;

use Carbon\Carbon;
use PedroACF\Invoicing\Responses\BaseResponse;
use Illuminate\Support\Arr;

class FechaHoraResponse extends BaseResponse
{
    public $date;

    public static function build($response){
        //dump($response);
        $data = $response->RespuestaFechaHora ?? (object)[];
        $object = new FechaHoraResponse();
        $object->buildBase($data);
        $object->date = new Carbon($data->fechaHora);
        return $object;
    }
}
