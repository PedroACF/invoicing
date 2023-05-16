<?php

namespace PedroACF\Invoicing\Services;

use Carbon\Carbon;
use PedroACF\Invoicing\Models\SIN\Activity;
use PedroACF\Invoicing\Models\SIN\ActivityDocSector;
use PedroACF\Invoicing\Models\SIN\CancelReason;
use PedroACF\Invoicing\Models\SIN\Cufd;
use PedroACF\Invoicing\Models\SIN\CurrencyType;
use PedroACF\Invoicing\Models\SIN\EmissionType;
use PedroACF\Invoicing\Models\SIN\IdentityDocType;
use PedroACF\Invoicing\Models\SIN\InvoiceType;
use PedroACF\Invoicing\Models\SIN\Legend;
use PedroACF\Invoicing\Models\SIN\Measurement;
use PedroACF\Invoicing\Models\SIN\Message;
use PedroACF\Invoicing\Models\SIN\PaymentType;
use PedroACF\Invoicing\Models\SIN\Product;
use PedroACF\Invoicing\Models\SIN\RoomType;
use PedroACF\Invoicing\Models\SIN\SalePointType;
use PedroACF\Invoicing\Models\SIN\SectorDocType;
use PedroACF\Invoicing\Models\SIN\SignificantEventType;
use PedroACF\Invoicing\Models\SIN\SourceCountry;
use PedroACF\Invoicing\Models\SYS\SalePoint;
use PedroACF\Invoicing\Models\SYS\SignificantEvent;
use PedroACF\Invoicing\Repositories\DataSyncRepository;
use PedroACF\Invoicing\Repositories\OperationRepository;
use PedroACF\Invoicing\Requests\DataSync\SincronizacionRequest;
use PedroACF\Invoicing\Requests\Operation\ConsultaEventoRequest;
use PedroACF\Invoicing\Requests\Operation\EventoSignificativoRequest;
use PedroACF\Invoicing\Responses\Operation\ListaEventosResponse;

class OperationService
{
    private $opeRepo;
    private $configService;
    private $codeService;
    public function __construct(OperationRepository $opeRepo, ConfigService $configService, CodeService $codeService)
    {
        $this->opeRepo = $opeRepo;
        $this->configService = $configService;
        $this->codeService = $codeService;
    }

    public function closeOperations(){
        // Cierre total de operaciones (inhabilita cuis y cufd actuales)
    }

    public function addSalePoint(string $name, string $description){

    }

    public function closeSalePoint(int $salePointCode){

    }

    public function checkSalePoint(){

    }

    public function getSignificantEvents(SalePoint $salePoint, Carbon $date): ListaEventosResponse{
        $request = new ConsultaEventoRequest(
            $salePoint,
            $date
        );
        return $this->opeRepo->getSignificantEvents($request);
    }

    public function finishAndSendSignificantEvent($salePoint, SignificantEvent $event): bool{
        $conn = $this->opeRepo->checkConnection();
        if($conn->transaccion){
            $start = new Carbon($event->start_datetime);
            $end = new Carbon($event->end_datetime);
            $request = new EventoSignificativoRequest(
                $salePoint,
                $event
            );
            $response = $this->opeRepo->addSignificantEvent($request);
            if($response->transaccion){
                $event->state = 'CLOSED';
                $event->reception_code = $response->codigoRecepcionEventoSignificativo;
                $event->save();
                return true;
            }
        }
        return false;
    }

    public function createSignificantEvent(SalePoint $salePoint, SignificantEventType $eventType, string $description): SignificantEvent{
        $event = new SignificantEvent();
        $event->event_type_code = $eventType->codigo_clasificador;
        $event->description = $description;
        //$event->reception_code = '';
        //$event->cafc = '';
        $cufdModel = $this->codeService->getActiveCufdModel($salePoint);//Sin considerar que esta fuera de plazo
        $event->event_cufd = $cufdModel->code;
        $event->start_datetime = $this->configService->getTime();
        $event->sale_point = $salePoint->sin_code;
        $event->save();
        return $event;
    }

    public function closeSignificantEvent(SignificantEvent $event, Cufd $cufd): SignificantEvent{
        $event->cufd = $cufd->code;
        $event->end_datetime = $this->configService->getTime();
        $event->state = 'CLOSING';
        $event->save();
        return $event;
    }

}
