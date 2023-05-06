<?php
namespace PedroACF\Invoicing\Responses\DataSync;

use Carbon\Carbon;
use PedroACF\Invoicing\Responses\BaseResponse;
use Illuminate\Support\Arr;

class FechaHoraResponse extends BaseResponse
{
    public $date;

    public static function build($response){
        $array = json_decode(json_encode($response), true);
        $resp = Arr::get($array, 'RespuestaFechaHora', []);
        $object = new FechaHoraResponse();
        $object->buildBase($resp);
        $object->date = new Carbon(Arr::get($resp, 'fechaHora'));
        return $object;
    }
}
