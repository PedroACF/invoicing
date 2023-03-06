<?php

namespace PedroACF\Invoicing\Requests\Code;

use PedroACF\Invoicing\Requests\BaseRequest;

class VerificarNitRequest extends BaseRequest{
    public $codigoAmbiente = 0;
    public $codigoModalidad = -1;
    public $codigoSistema = "";
    public $codigoSucursal = -1;//0=>Casa matriz
    public $cuis = "";
    public $nit = "";
    public $nitParaVerificacion = "";

    public function __construct($cuis, $nitParaVerificacion)
    {
        $this->codigoAmbiente = config("siat_invoicing.enviroment");
        $this->codigoModalidad = config("siat_invoicing.mode");
        $this->codigoSistema = config("siat_invoicing.system_code");
        $this->codigoSucursal = config("siat_invoicing.office");
        $this->cuis = $cuis;
        $this->nit = config("siat_invoicing.nit");
        $this->nitParaVerificacion = $nitParaVerificacion;
    }
}
