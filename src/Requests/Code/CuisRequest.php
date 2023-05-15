<?php

namespace PedroACF\Invoicing\Requests\Code;

use PedroACF\Invoicing\Models\SYS\SalePoint;
use PedroACF\Invoicing\Requests\BaseRequest;
use PedroACF\Invoicing\Services\ConfigService;

class CuisRequest extends BaseRequest
{
    public $codigoAmbiente = 0;
    public $codigoModalidad = 0;
    public $codigoPuntoVenta = -1;
    public $codigoSistema = '';
    public $codigoSucursal = -1;//0=>Casa matriz
    public $nit = "";

    public function __construct(SalePoint $salePoint)
    {
        $config = app(ConfigService::class);
        $this->requestName = "SolicitudCuis";
        $this->codigoAmbiente = $config->getEnvironment();
        $this->codigoModalidad = $config->getInvoiceMode();
        $this->codigoPuntoVenta = $salePoint->sin_code;
        $this->codigoSistema = $config->getSystemCode();
        $this->codigoSucursal = $config->getOfficeCode();
        $this->nit = $config->getNit();
    }

}
