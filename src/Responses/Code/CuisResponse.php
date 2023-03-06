<?php
namespace PedroACF\Invoicing\Responses\Code;

use PedroACF\Invoicing\Responses\BaseResponse;
use Carbon\Carbon;
use Illuminate\Support\Arr;
use stdClass;
class CuisResponse extends BaseResponse
{
    public $codigo = "";
    public $fechaVigencia = "";

    public static function build($response){
        $array = json_decode(json_encode($response), true);
        $resp = Arr::get($array, 'RespuestaCuis', []);
        $object = new CuisResponse();
        $object->buildBase($resp);
        $object->codigo = Arr::get($resp, 'codigo', "");
        $fechaVigencia = Arr::get($resp, 'fechaVigencia', '');
        $object->fechaVigencia = strlen($fechaVigencia)>0? new Carbon($fechaVigencia): null;
        return $object;
    }
}
