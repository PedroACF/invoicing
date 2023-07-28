<?php
namespace PedroACF\Invoicing\Responses\Operation;

use PedroACF\Invoicing\Responses\BaseResponse;
use Illuminate\Support\Arr;

class ConsultaPuntoVentaResponse extends BaseResponse
{
    public $salePoints = [];

    public static function build($response){
        $data = $response->RespuestaConsultaPuntoVenta ?? (object)[];
        $object = new ConsultaPuntoVentaResponse();
        $salePoints = $data->listaPuntosVentas ?? [];
        $list = is_array($salePoints)? $salePoints: [$salePoints];
        foreach ($list as $salePoint){
            $remote = RemoteSalePoint::build($salePoint);
            $object->salePoints[] = $remote;
        }
        $object->buildBase($data);
        return $object;
    }
}

class RemoteSalePoint{
    public $codigoPuntoVenta;
    public $nombrePuntoVenta;
    public $tipoPuntoVenta;

    public static function build($data): RemoteSalePoint{
        $object = new RemoteSalePoint();
        $object->codigoPuntoVenta = $data->codigoPuntoVenta??-1;
        $object->nombrePuntoVenta = $data->nombrePuntoVenta??'';
        $object->tipoPuntoVenta = $data->tipoPuntoVenta??'';
        return $object;
    }
}
