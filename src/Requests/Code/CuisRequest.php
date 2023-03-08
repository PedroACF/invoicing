<?php

namespace PedroACF\Invoicing\Requests\Code;

use PedroACF\Invoicing\Requests\BaseRequest;
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
        $tokenReg = TokenUtils::getValidTokenReg();
        $this->requestName = "SolicitudCuis";
        $this->codigoAmbiente = config("siat_invoicing.enviroment");
        $this->codigoModalidad = config("siat_invoicing.mode");
        $this->codigoPuntoVenta = 0;
        $this->codigoSistema = config("siat_invoicing.system_code");
        $this->codigoSucursal = $tokenReg->sucursal;
        $this->nit = $tokenReg->nit;
    }

}
