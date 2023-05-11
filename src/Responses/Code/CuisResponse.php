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
        $data = $response->RespuestaCuis ?? (object)[];
        $object = new CuisResponse();
        $object->buildBase($data);
        $object->codigo = $data->codigo ?? '';
        $object->fechaVigencia = isset($data->fechaVigencia)? new Carbon($data->fechaVigencia): null;
        return $object;
    }
}
