<?php
namespace PedroACF\Invoicing\Requests\Operation;
use PedroACF\Invoicing\Requests\BaseRequest;
use PedroACF\Invoicing\Services\ConfigService;
use PedroACF\Invoicing\Utils\TokenUtils;

class EventoSignificativoRequest extends BaseRequest{
    public $codigoAmbiente = 0;
    public $codigoMotivoEvento = 0;
    public $codigoPuntoVenta = -1;
    public $codigoSistema = '';
    public $codigoSucursal = -1;//0=>Casa matriz
    public $cuis = "";
    public $nit = "";
    public $cufd = "";
    public $cufdEvento = "";
    public $descripcion = "";
    public $fechaHoraInicioEvento = null;
    public $fechaHoraFinEvento = null;

    public function __construct($cuis)
    {
        $currentConfig = ConfigService::getConfigs();
        $this->requestName = "SolicitudEventoSignificativo";
        $this->codigoAmbiente = config("siat_invoicing.enviroment");
        $this->codigoPuntoVenta = $currentConfig->sale_point;
        $this->codigoSistema = config("siat_invoicing.system_code");
        $this->codigoSucursal = $currentConfig->office;
        $this->cuis = $cuis;
        $this->nit = $currentConfig->nit;
    }
}
