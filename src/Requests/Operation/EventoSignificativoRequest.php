<?php
namespace PedroACF\Invoicing\Requests\Operation;
use Carbon\Carbon;
use PedroACF\Invoicing\Models\SYS\Config;
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

    public function __construct(int $salePoint, int $eventTypeCode, string $description, string $cufdEvent, Carbon $startDate, Carbon $endDate)
    {
        $config = app(ConfigService::class);
        $code = app(CodeService::class);
        //Forzar nuevo cufd para pruebas
        //TODO: Arreglar
        $cufd = $code->getCufdCode($salePoint, true);

        $this->requestName = "SolicitudEventoSignificativo";
        $this->codigoAmbiente = $config->getEnvironment();
        $this->codigoPuntoVenta = $salePoint;
        $this->codigoSistema = $config->getSystemCode();
        $this->codigoSucursal = $config->getOfficeCode();
        $this->cuis = $code->getCuisCode($salePoint);
        $this->nit = $config->getNit();

        //cositas
        $this->codigoMotivoEvento = $eventTypeCode;
        $this->descripcion = $description;
        $this->cufdEvento = $cufdEvent;
        //TODO: Probando
        $this->cufd = $cufd;
        $this->fechaHoraInicioEvento = $startDate->format("Y-m-d\TH:i:s.v");
        $this->fechaHoraFinEvento = $endDate->format("Y-m-d\TH:i:s.v");
    }
}
