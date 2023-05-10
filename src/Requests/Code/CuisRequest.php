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

    public function __construct()
    {
        $this->requestName = "SolicitudCuis";
        $this->codigoAmbiente = config("siat_invoicing.enviroment");
        $this->codigoModalidad = config("siat_invoicing.mode");
        $this->codigoPuntoVenta = $currentConfig->sale_point;
        $this->codigoSistema = config("siat_invoicing.system_code");
        $this->codigoSucursal = Config::getOfficeCodeConfig()->value;
        $this->nit = Config::getNitConfig()->value;
    }

}
