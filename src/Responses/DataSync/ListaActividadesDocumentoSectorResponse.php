<?php
namespace PedroACF\Invoicing\Responses\DataSync;

use PedroACF\Invoicing\Responses\BaseResponse;
use Illuminate\Support\Arr;

class ListaActividadesDocumentoSectorResponse extends BaseResponse
{
    public $items = [];

    public static function build($response){

        $data = $response->RespuestaListaActividadesDocumentoSector ?? (object)[];
        $object = new ListaActividadesDocumentoSectorResponse();
        $object->buildBase($data);
        $list = [];
        foreach($data->listaActividadesDocumentoSector??[] as $item){
            $list[] = new ActividadDocumentoSector(
                $item->codigoActividad?? '',
                $item->codigoDocumentoSector?? '',
                $item->tipoDocumentoSector??''
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
