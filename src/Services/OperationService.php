<?php

namespace PedroACF\Invoicing\Services;

use Carbon\Carbon;
use PedroACF\Invoicing\Models\SIN\Activity;
use PedroACF\Invoicing\Models\SIN\ActivityDocSector;
use PedroACF\Invoicing\Models\SIN\CancelReason;
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
use PedroACF\Invoicing\Models\SYS\SignificantEvent;
use PedroACF\Invoicing\Repositories\DataSyncRepository;
use PedroACF\Invoicing\Repositories\OperationRepository;
use PedroACF\Invoicing\Requests\DataSync\SincronizacionRequest;
use PedroACF\Invoicing\Requests\Operation\EventoSignificativoRequest;

class OperationService
{
    private $opeRepo;
    public function __construct(OperationRepository $opeRepo)
    {
        $this->opeRepo = $opeRepo;
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

    public function addSignificantEvent($salePoint, SignificantEvent $event): bool{
        $conn = $this->opeRepo->checkConnection();
        if($conn->transaccion){
            $start = new Carbon($event->start_datetime);
            $end = Carbon::now();
            $end->subHours(1);
            $event->end_datetime = $end;
            $event->save();
            $request = new EventoSignificativoRequest(
                $salePoint,
                $event->event_code,
                $event->description,
                $event->event_cufd,
                $start,
                $end
            );
            dump($request);
            $response = $this->opeRepo->addSignificantEvent($request);
            dump($response);
            return true;
        }
        return false;
    }

    public function checkSignificantEvent(){

    }

}
