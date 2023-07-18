<?php
namespace PedroACF\Invoicing\Requests\Operation;
use Carbon\Carbon;
use PedroACF\Invoicing\Models\SYS\Config;
use PedroACF\Invoicing\Models\SYS\SalePoint;
use PedroACF\Invoicing\Models\SYS\SignificantEvent;
use PedroACF\Invoicing\Requests\BaseRequest;
use PedroACF\Invoicing\Services\CodeService;
use PedroACF\Invoicing\Services\ConfigService;
use PedroACF\Invoicing\Utils\TokenUtils;

class ConsultaPuntoVentaRequest extends BaseRequest{
    public $codigoAmbiente = 0;
    public $codigoSistema = '';
    public $codigoSucursal = -1;//0=>Casa matriz
    public $cuis = "";
    public $nit = "";
    public function __construct(SalePoint $salePoint)
    {
        $config = app(ConfigService::class);
        $this->requestName = "SolicitudConsultaPuntoVenta";
        $this->codigoAmbiente = $config->getEnvironment();
        $this->codigoSistema = $config->getSystemCode();
        $this->codigoSucursal = $config->getOfficeCode();
        $this->cuis = $salePoint->active_cuis;
        $this->nit = $config->getNit();
    }
}
