<?php
namespace PedroACF\Invoicing\Requests\PurchaseSale;
use Carbon\Carbon;
use PedroACF\Invoicing\Models\SYS\Config;
use PedroACF\Invoicing\Models\SYS\SalePoint;
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


    public function __construct(SalePoint $salePoint, $sectorDocumentCode, $emissionCode, $invoiceType, $reasonCode, $cuf)
    {
        $config = app(ConfigService::class);
        $cufd = $salePoint->cufdCodes()->where('state','ACTIVE')->first();
        $this->requestName = "SolicitudServicioAnulacionFactura";
        $this->codigoAmbiente = $config->getEnvironment();
        $this->codigoDocumentoSector = $sectorDocumentCode; // TODO: DE LA BD?
        $this->codigoEmision = $emissionCode; //TODO: Verificar
        $this->codigoModalidad = $config->getInvoiceMode();
        $this->codigoPuntoVenta = $salePoint->sin_code;
        $this->codigoSistema = $config->getSystemCode();
        $this->codigoSucursal = $config->getOfficeCode();
        $this->cufd = $cufd->code;
        $this->cuis = $salePoint->active_cuis;
        $this->nit = $config->getNit();

        $this->tipoFacturaDocumento = $invoiceType;
        $this->codigoMotivo = $reasonCode;
        $this->cuf = $cuf;
    }
}
