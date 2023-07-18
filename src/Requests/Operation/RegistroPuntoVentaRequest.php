<?php
namespace PedroACF\Invoicing\Requests\Operation;
use Carbon\Carbon;
use PedroACF\Invoicing\Models\SIN\SalePointType;
use PedroACF\Invoicing\Models\SYS\Config;
use PedroACF\Invoicing\Models\SYS\SalePoint;
use PedroACF\Invoicing\Models\SYS\SignificantEvent;
use PedroACF\Invoicing\Requests\BaseRequest;
use PedroACF\Invoicing\Services\CodeService;
use PedroACF\Invoicing\Services\ConfigService;
use PedroACF\Invoicing\Utils\TokenUtils;

class RegistroPuntoVentaRequest extends BaseRequest{
    public $codigoAmbiente = 0;
    public $codigoModalidad = 0;
    public $codigoSistema = '';
    public $codigoSucursal = -1;//0=>Casa matriz
    public $codigoTipoPuntoVenta = 0;
    public $cuis = "";
    public $descripcion = "";
    public $nit = "";
    public $nombrePuntoVenta = "";
    public function __construct(SalePoint $salePoint, SalePointType $salePointType, string $salePointName, string $salePointDescription)
    {
        $config = app(ConfigService::class);
        $this->requestName = "SolicitudRegistroPuntoVenta";
        $this->codigoAmbiente = $config->getEnvironment();
        $this->codigoModalidad = $config->getInvoiceMode();
        $this->codigoSistema = $config->getSystemCode();
        $this->codigoSucursal = $config->getOfficeCode();

        $this->codigoTipoPuntoVenta = $salePointType->codigo_clasificador;

        $this->cuis = $salePoint->active_cuis;
        $this->nit = $config->getNit();

        $this->nombrePuntoVenta = $salePointName;
        $this->descripcion = $salePointDescription;
    }
}
