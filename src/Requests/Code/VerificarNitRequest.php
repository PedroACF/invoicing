<?php

namespace PedroACF\Invoicing\Requests\Code;

use PedroACF\Invoicing\Models\SYS\Config;
use PedroACF\Invoicing\Models\SYS\SalePoint;
use PedroACF\Invoicing\Requests\BaseRequest;
use PedroACF\Invoicing\Services\ConfigService;

class VerificarNitRequest extends BaseRequest{
    public $codigoAmbiente = 0;
    public $codigoModalidad = -1;
    public $codigoSistema = "";
    public $codigoSucursal = -1;//0=>Casa matriz
    public $cuis = "";
    public $nit = "";
    public $nitParaVerificacion = "";

    public function __construct(SalePoint $salePoint, string $nitForCheck){
        $config = app(ConfigService::class);
        $this->requestName = "SolicitudVerificarNit";
        $this->codigoAmbiente = $config->getEnvironment();
        $this->codigoModalidad = $config->getInvoiceMode();
        $this->codigoSistema = $config->getSystemCode();
        $this->codigoSucursal = $config->getOfficeCode();
        $this->cuis = $salePoint->active_cuis;
        $this->nit = $config->getNit();
        $this->nitParaVerificacion = $nitForCheck;
    }
}
