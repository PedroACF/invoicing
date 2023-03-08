<?php
namespace PedroACF\Invoicing\Responses\DataSync;

use PedroACF\Invoicing\Responses\BaseResponse;
use Illuminate\Support\Arr;

class ListaActividadesDocumentoSectorResponse extends BaseResponse
{
    public $items = [];

    public static function build($response){
        $array = json_decode(json_encode($response), true);
        $resp = Arr::get($array, 'RespuestaListaActividadesDocumentoSector', []);
        $object = new ListaActividadesDocumentoSectorResponse();
        $object->buildBase($resp);

        $list = [];
        foreach (Arr::get($resp, 'listaActividadesDocumentoSector', [] ) as $item){
            $list[] = new ActividadDocumentoSector(
                Arr::get($item, 'codigoActividad', ""),
                Arr::get($item, 'codigoDocumentoSector', ""),
                Arr::get($item, 'tipoDocumentoSector', "")
            );
        }
        $object->items = $list;
        return $object;
    }
}

class ActividadDocumentoSector{
    public $codigoActividad = "";
    public $codigoDocumentoSector = "";
    public $tipoDocumentoSector = "";

    public function __construct($codigoActividad, $codigoDocSector, $tipoDocSector){
        $this->codigoActividad = $codigoActividad;
        $this->codigoDocumentoSector = $codigoDocSector;
        $this->tipoDocumentoSector = $tipoDocSector;
    }
}
