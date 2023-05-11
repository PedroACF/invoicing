<?php

namespace PedroACF\Invoicing\Requests\Code;

use PedroACF\Invoicing\Models\SYS\Config;
use PedroACF\Invoicing\Requests\BaseRequest;
use PedroACF\Invoicing\Services\ConfigService;
use PedroACF\Invoicing\Utils\TokenUtils;

class CuisRequest extends BaseRequest
{
    public $codigoAmbiente = 0;
    public $codigoModalidad = 0;
    public $codigoPuntoVenta = -1;
    public $codigoSistema = '';
    public $codigoSucursal = -1;//0=>Casa matriz
    public $nit = "";

    public function __construct($salePoint = 0)
    {
        $config = app(ConfigService::class);
        $this->requestName = "SolicitudCuis";
        $this->codigoAmbiente = $config->getEnvironment();
        $this->codigoModalidad = $config->getInvoiceMode();
        $this->codigoPuntoVenta = $salePoint;
        $this->codigoSistema = $config->getSystemCode();
        $this->codigoSucursal = $config->getOfficeCode();
        $this->nit = $config->getNit();
    }

}
