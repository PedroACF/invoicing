<?php

namespace PedroACF\Invoicing\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
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
use PedroACF\Invoicing\Repositories\PurchaseSaleRepository;
use PedroACF\Invoicing\Requests\DataSync\SincronizacionRequest;
use PedroACF\Invoicing\Requests\Operation\ConsultaEventoRequest;
use PedroACF\Invoicing\Requests\Operation\EventoSignificativoRequest;
use PedroACF\Invoicing\Requests\PurchaseSale\RecepcionPaqueteFacturaRequest;
use PedroACF\Invoicing\Responses\Operation\ListaEventosResponse;
use PedroACF\Invoicing\Utils\Packer;
use PedroACF\Invoicing\Utils\XmlSigner;

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

    public function finishAndSendSignificantEvent($salePoint, SignificantEvent $event, $invoices = []): bool{
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
                $now = Carbon::now();
                $path = public_path('vendor/pacf_invoicing/temp_files/pkg_'.$now->getTimestampMs().'.tar');
                $this->deleteOldFiles();
                $packer = new Packer($path);
                foreach ($invoices as $i=>$invoice){
                    $emissionDate = $this->configService->getTime();
                    // COMPLETE INVOICE
                    $invoiceNumber = $this->configService->getAvailableInvoiceNumber();
                    $invoice->header->fechaEmision = $emissionDate->format("Y-m-d\TH:i:s.v");
                    $invoice->header->nitEmisor = $this->configService->getNit();
                    $invoice->header->razonSocialEmisor = $this->configService->getBusinessName();
                    $invoice->header->municipio = $this->configService->getMunicipality();
                    $invoice->header->telefono = $this->configService->getOfficePhone();
                    $invoice->header->numeroFactura = $invoiceNumber;
                    $invoice->header->cufd = $event->event_cufd;
                    $invoice->header->codigoSucursal = $this->configService->getOfficeCode();
                    $invoice->header->direccion = $this->configService->getOfficeAddress();
                    $invoice->header->codigoDocumentoSector = $this->configService->getSectorDocumentCode();
                    $invoice->header->generateCufCode($salePoint, Cufd::where('code', $event->event_cufd)->first(), 2);

                    // FIRMAR XML
                    $signer = app(XmlSigner::class);
                    $signedXML = $signer->sign($invoice->toXml()->saveXML());
                    $packer->addFromString("factura_$i.xml", $signedXML);
                }
                $count = count($invoices);
                $compress = $packer->compress(\Phar::GZ);
                $file = file_get_contents($path.'.gz');
                $hash = hash('sha256', $file);
                $packageRequest = new RecepcionPaqueteFacturaRequest($salePoint, 2, 1, $file, $hash, null, $count, $event->reception_code);
                $packageRepo = new PurchaseSaleRepository();
                $response = $packageRepo->sendInvoicePackage($packageRequest);
                if($response->transaccion){
                    // TODO: Set codigo recepcion
                }
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

    private function deleteOldFiles() {
        $path = public_path('vendor/pacf_invoicing/temp_files');
        $files = collect(File::allFiles($path));
        $files->each(function ($file) {
            $lastModified =  File::lastModified($file);
            $lastModified = Carbon::parse($lastModified);
            if (Carbon::now()->gt($lastModified->addHours(2))) {
                File::delete($file);
            }
        });
        return true;
    }

}
