<?php
namespace PedroACF\Invoicing\Requests\PurchaseSale;
use Carbon\Carbon;
use PedroACF\Invoicing\Models\SYS\Config;
use PedroACF\Invoicing\Models\SYS\SalePoint;
use PedroACF\Invoicing\Requests\BaseRequest;
use PedroACF\Invoicing\Services\ConfigService;
use PedroACF\Invoicing\Utils\TokenUtils;

class RecepcionPaqueteFacturaRequest extends BaseRequest
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
    public $archivo = '';
    public $fechaEnvio = null;
    public $hashArchivo = '';
    public $cafc = null;
    public $cantidadFacturas = 0;
    public $codigoEvento = null;

    public function __construct(SalePoint $salePoint, $emissionCode, $invoiceType, $file, $hash, $cafc, $invoicesCount, $eventCode)
    {
        $cufd = $salePoint->cufdCodes()->where('state','ACTIVE')->first();
        $config = app(ConfigService::class);
        //$config = new ConfigService();
        $this->requestName = "SolicitudServicioRecepcionPaquete";
        $this->codigoAmbiente = $config->getEnvironment();
        $this->codigoDocumentoSector = $config->getSectorDocumentCode();
        $this->codigoEmision = $emissionCode; //TODO: Verificar
        $this->codigoModalidad = $config->getInvoiceMode();
        $this->codigoPuntoVenta = $salePoint->sin_code;
        $this->codigoSistema = $config->getSystemCode();
        $this->codigoSucursal = $config->getOfficeCode();
        $this->cufd = $cufd->code;
        $this->cuis = $salePoint->active_cuis;
        $this->nit = $config->getNit();

        $this->tipoFacturaDocumento = $invoiceType;
        $this->archivo = $file;
        $this->fechaEnvio = Carbon::now()->format("Y-m-d\TH:i:s.v");
        $this->hashArchivo = $hash;
        $this->cafc = $cafc;
        $this->cantidadFacturas = $invoicesCount;
        $this->codigoEvento = $eventCode;
    }
}
