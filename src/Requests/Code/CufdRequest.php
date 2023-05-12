<?php
namespace PedroACF\Invoicing\Requests\Code;

use PedroACF\Invoicing\Models\SYS\Config;
use PedroACF\Invoicing\Requests\BaseRequest;
use PedroACF\Invoicing\Services\ConfigService;
use PedroACF\Invoicing\Utils\TokenUtils;

class CufdRequest extends BaseRequest
{
    public $codigoAmbiente = 0;
    public $codigoModalidad = 0;
    public $codigoPuntoVenta = -1;
    public $codigoSistema = '';
    public $codigoSucursal = -1;//0=>Casa matriz
    public $cuis = "";
    public $nit = "";

    public function __construct($salePoint, $cuis)
    {
        $config = app(ConfigService::class);
        $this->requestName = "SolicitudCufd";
        $this->codigoAmbiente = $config->getEnvironment();
        $this->codigoModalidad = $config->getInvoiceMode();
        $this->codigoPuntoVenta = $salePoint;
        $this->codigoSistema = $config->getSystemCode();
        $this->codigoSucursal = $config->getOfficeCode();
        $this->cuis = $cuis;
        $this->nit = $config->getNit();
    }
}
