<?php
namespace PedroACF\Invoicing\Responses\Code;
use PedroACF\Invoicing\Responses\BaseResponse;
use Illuminate\Support\Arr;
use Carbon\Carbon;
use stdClass;
class CufdResponse extends BaseResponse
{
    public $codigo = "";
    public $codigoControl = "";
    public $fechaVigencia = "";

    public static function build($response){
        $data = $response->RespuestaCufd ?? (object)[];
        $object = new CufdResponse();
        $object->buildBase($data);
        $object->codigo = $data->codigo??'';
        $object->codigoControl = $data->codigoControl??'';
        $object->fechaVigencia = isset($data->fechaVigencia)? new Carbon($data->fechaVigencia): null;
        return $object;
    }
}
