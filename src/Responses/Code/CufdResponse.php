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
    public $direccion = "";
    public $fechaVigencia = "";

    public static function build($response){
        $array = json_decode(json_encode($response), true);
        $resp = Arr::get($array, 'RespuestaCufd', []);
        $object = new CufdResponse();
        $object->buildBase($resp);
        $object->codigo = Arr::get($resp, 'codigo', "");
        $object->codigoControl = Arr::get($resp, 'codigoControl', "");
        $object->direccion = Arr::get($resp, 'direccion', "");
        $fechaVigencia = Arr::get($resp, 'fechaVigencia', '');
        $object->fechaVigencia = strlen($fechaVigencia)>0? new Carbon($fechaVigencia): null;
        return $object;
    }
}
