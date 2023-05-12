<?php
namespace PedroACF\Invoicing\Responses\DataSync;

use PedroACF\Invoicing\Responses\BaseResponse;
use Illuminate\Support\Arr;

class ListaParametricasResponse extends BaseResponse
{
    public $items = [];

    public static function build($response){
        $data = $response->RespuestaListaParametricas ?? (object)[];
        $object = new ListaParametricasResponse();
        $object->buildBase($data);
        $items = [];
        foreach ($data->listaCodigos ?? [] as $item){
            $items[] = new ParamCodigo(
                $item->codigoClasificador??'',
                $item->descripcion??''
            );
        }
        $object->items = $items;
        return $object;
    }
}

class ParamCodigo{
    public $codigoClasificador = "";
    public $descripcion = "";

    public function __construct($cod, $desc){
        $this->codigoClasificador = $cod;
        $this->descripcion = $desc;
    }
}
