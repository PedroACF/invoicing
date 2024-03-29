<?php
namespace PedroACF\Invoicing\Requests\DataSync;
use PedroACF\Invoicing\Models\SYS\SalePoint;
use PedroACF\Invoicing\Requests\BaseRequest;
use PedroACF\Invoicing\Services\ConfigService;

class SincronizacionRequest extends BaseRequest
{
    public $codigoAmbiente = 0;
    public $codigoPuntoVenta = -1;
    public $codigoSistema = '';
    public $codigoSucursal = -1;//0=>Casa matriz
    public $cuis = "";
    public $nit = "";

    public function __construct(SalePoint $salePoint)
    {
        $config = app(ConfigService::class);
        $this->requestName = "SolicitudSincronizacion";
        $this->codigoAmbiente = $config->getEnvironment();
        $this->codigoPuntoVenta = $salePoint->sin_code;
        $this->codigoSistema = $config->getSystemCode();
        $this->codigoSucursal = $config->getOfficeCode();
        $this->cuis = $salePoint->active_cuis;
        $this->nit = $config->getNit();
        //dump($this->toArray());
    }
}
