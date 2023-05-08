<?php

namespace PedroACF\Invoicing\Requests\Code;

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

    public function __construct($cuis, $nitParaVerificacion){
        $currentConfig = ConfigService::getConfigs();
        $this->requestName = "SolicitudVerificarNit";
        $this->codigoAmbiente = config("siat_invoicing.enviroment");
        $this->codigoModalidad = config("siat_invoicing.mode");
        $this->codigoSistema = config("siat_invoicing.system_code");
        $this->codigoSucursal = $currentConfig->office;
        $this->cuis = $cuis;
        $this->nit = $currentConfig->nit;
        $this->nitParaVerificacion = $nitParaVerificacion;
    }
}
