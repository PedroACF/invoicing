<?php
namespace PedroACF\Invoicing\Requests\DataSync;
use PedroACF\Invoicing\Requests\BaseRequest;

class SincronizacionRequest extends BaseRequest
{
    public $codigoAmbiente = 0;
    public $codigoPuntoVenta = -1;
    public $codigoSistema = '';
    public $codigoSucursal = -1;//0=>Casa matriz
    public $cuis = "";
    public $nit = "";

    public function __construct($cuis)
    {
        $this->requestName = "SolicitudSincronizacion";
        $this->codigoAmbiente = config("siat_invoicing.enviroment");
        $this->codigoPuntoVenta = 0;
        $this->codigoSistema = config("siat_invoicing.system_code");
        $this->codigoSucursal = config("siat_invoicing.office");
        $this->cuis = $cuis;
        $this->nit = config("siat_invoicing.nit");
    }
}
