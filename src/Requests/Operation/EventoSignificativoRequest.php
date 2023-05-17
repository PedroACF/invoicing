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

    public function __construct(SalePoint $salePoint, SignificantEvent $event)
    {
        $config = app(ConfigService::class);

        $this->requestName = "SolicitudEventoSignificativo";
        $this->codigoAmbiente = $config->getEnvironment();
        $this->codigoPuntoVenta = $salePoint->sin_code;
        $this->codigoSistema = $config->getSystemCode();
        $this->codigoSucursal = $config->getOfficeCode();
        $this->cuis = $salePoint->active_cuis;
        $this->nit = $config->getNit();

        //cositas
        $this->codigoMotivoEvento = $event->event_type_code;
        $this->descripcion = $event->description;
        $this->cufdEvento = $event->event_cufd;
        //TODO: Probando
        $this->cufd = $event->cufd;
        $start = new Carbon($event->start_datetime);
        $end = new Carbon($event->end_datetime);
        $this->fechaHoraInicioEvento = $start->format("Y-m-d\TH:i:s.v");
        $this->fechaHoraFinEvento = $end->format("Y-m-d\TH:i:s.v");
    }
}
