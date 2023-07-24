<?php
namespace PedroACF\Invoicing\Requests\PurchaseSale;
use Carbon\Carbon;
use PedroACF\Invoicing\Models\SIN\EmissionType;
use PedroACF\Invoicing\Models\SYS\Config;
use PedroACF\Invoicing\Models\SYS\SalePoint;
use PedroACF\Invoicing\Requests\BaseRequest;
use PedroACF\Invoicing\Services\ConfigService;
use PedroACF\Invoicing\Utils\TokenUtils;

class ValidacionRecepcionPaqueteRequest extends BaseRequest
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
    public $codigoRecepcion;

    public function __construct(SalePoint $salePoint, $invoiceType, $receptionCode)
    {
        $offlineEmission = EmissionType::where("descripcion", "FUERA DE LINEA")->first();
        $cufd = $salePoint->cufdCodes()->where('state','ACTIVE')->first();
        $config = app(ConfigService::class);
        //$config = new ConfigService();
        $this->requestName = "SolicitudServicioValidacionRecepcionPaquete";
        $this->codigoAmbiente = $config->getEnvironment();
        $this->codigoDocumentoSector = $config->getSectorDocumentCode();
        $this->codigoEmision = $offlineEmission->codigo_clasificador;
        $this->codigoModalidad = $config->getInvoiceMode();
        $this->codigoPuntoVenta = $salePoint->sin_code;
        $this->codigoSistema = $config->getSystemCode();
        $this->codigoSucursal = $config->getOfficeCode();
        $this->cufd = $cufd->code;
        $this->cuis = $salePoint->active_cuis;
        $this->nit = $config->getNit();

        $this->tipoFacturaDocumento = $invoiceType;
        $this->codigoRecepcion = $receptionCode;
    }
}
