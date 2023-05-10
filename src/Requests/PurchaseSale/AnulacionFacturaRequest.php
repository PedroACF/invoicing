<?php
namespace PedroACF\Invoicing\Requests\PurchaseSale;
use Carbon\Carbon;
use PedroACF\Invoicing\Models\SYS\Config;
use PedroACF\Invoicing\Requests\BaseRequest;
use PedroACF\Invoicing\Services\ConfigService;
use PedroACF\Invoicing\Utils\TokenUtils;

class AnulacionFacturaRequest extends BaseRequest
{
    public $codigoAmbiente = 0;
    public $codigoDocumentoSector = 0;
    public $codigoEmision = 0;
    public $codigoModalidad = 0;
    public $codigoPuntoVenta = -1;
    public $codigoSistema = '';
    public $codigoSucursal = -1;//0=>Casa matriz
    public $cufd = '';
    public $cuis = "";
    public $nit = "";
    public $tipoFacturaDocumento = 0;
    public $codigoMotivo = '';
    public $cuf = 0;


    public function __construct($sectorDocumentCode, $emissionCode, $cufd, $cuis, $invoiceType, $reasonCode, $cuf)
    {
        $this->requestName = "SolicitudServicioAnulacionFactura";
        $this->codigoAmbiente = config("siat_invoicing.enviroment");
        $this->codigoDocumentoSector = $sectorDocumentCode; // TODO: DE LA BD?
        $this->codigoEmision = $emissionCode; //TODO: Verificar
        $this->codigoModalidad = config("siat_invoicing.mode");
        $this->codigoPuntoVenta = $currentConfig->sale_point;
        $this->codigoSistema = config("siat_invoicing.system_code");
        $this->codigoSucursal = Config::getOfficeCodeConfig()->value;
        $this->cufd = $cufd;
        $this->cuis = $cuis;
        $this->nit = Config::getNitConfig()->value;

        $this->tipoFacturaDocumento = $invoiceType;
        $this->codigoMotivo = $reasonCode;
        $this->cuf = $cuf;
    }
}
