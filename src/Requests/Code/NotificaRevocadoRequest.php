<?php

namespace PedroACF\Invoicing\Requests\Code;

use PedroACF\Invoicing\Requests\BaseRequest;

class NotificaRevocadoRequest extends BaseRequest{
    public $certificado = "";
    public $codigoAmbiente = 0;
    public $codigoSistema = "";
    public $codigoSucursal = -1;//0=>Casa matriz
    public $cuis = "";
    public $fechaRevocacion = "";
    public $nit = "";
    public $razonRevocacion = "";

    public function __construct($cuis, $certificado, $fechaRevocacion, $razonRevocacion)
    {
        $this->certificado = $certificado;
        $this->codigoAmbiente = config("siat_invoicing.enviroment");
        $this->codigoSistema = config("siat_invoicing.system_code");
        $this->codigoSucursal = config("siat_invoicing.office");
        $this->cuis = $cuis;
        $this->nit = config("siat_invoicing.nit");
        $this->fechaRevocacion = $fechaRevocacion;
        $this->razonRevocacion = $razonRevocacion;
    }
}
