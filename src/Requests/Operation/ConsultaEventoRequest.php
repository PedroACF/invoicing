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

class ConsultaEventoRequest extends BaseRequest{
    public $codigoAmbiente = 0;
    public $codigoPuntoVenta = -1;
    public $codigoSistema = '';
    public $codigoSucursal = -1;//0=>Casa matriz
    public $cuis = "";
    public $nit = "";
    public $cufd = "";
    public $fechaEvento = null;

    public function __construct(SalePoint $salePoint, Carbon $date)
    {


        $config = app(ConfigService::class);
        $cufdModel = $salePoint->cufdCodes()->where('state', 'ACTIVE')->first();
        $this->requestName = "SolicitudConsultaEvento";
        $this->codigoAmbiente = $config->getEnvironment();
        $this->codigoPuntoVenta = $salePoint->sin_code;
        $this->codigoSistema = $config->getSystemCode();
        $this->codigoSucursal = $config->getOfficeCode();
        $this->cuis = $salePoint->active_cuis;
        $this->cufd = $cufdModel? $cufdModel->code: '';
        $this->nit = $config->getNit();

        $this->fechaEvento = $date->format('Y-m-d');
    }
}
