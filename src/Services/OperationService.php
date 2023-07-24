<?php

namespace PedroACF\Invoicing\Services;

use Carbon\Carbon;
use Illuminate\Support\Arr;
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
use PedroACF\Invoicing\Models\SYS\Package;
use PedroACF\Invoicing\Models\SYS\Sale;
use PedroACF\Invoicing\Models\SYS\SalePoint;
use PedroACF\Invoicing\Models\SYS\SignificantEvent;
use PedroACF\Invoicing\Repositories\DataSyncRepository;
use PedroACF\Invoicing\Repositories\OperationRepository;
use PedroACF\Invoicing\Repositories\PurchaseSaleRepository;
use PedroACF\Invoicing\Requests\DataSync\SincronizacionRequest;
use PedroACF\Invoicing\Requests\Operation\CierrePuntoVentaRequest;
use PedroACF\Invoicing\Requests\Operation\ConsultaEventoRequest;
use PedroACF\Invoicing\Requests\Operation\ConsultaPuntoVentaRequest;
use PedroACF\Invoicing\Requests\Operation\EventoSignificativoRequest;
use PedroACF\Invoicing\Requests\Operation\RegistroPuntoVentaRequest;
use PedroACF\Invoicing\Requests\PurchaseSale\RecepcionPaqueteFacturaRequest;
use PedroACF\Invoicing\Responses\Operation\ConsultaPuntoVentaResponse;
use PedroACF\Invoicing\Responses\Operation\ListaEventosResponse;
use PedroACF\Invoicing\Responses\Operation\RegistroPuntoVentaResponse;
use PedroACF\Invoicing\Utils\Packer;
use PedroACF\Invoicing\Utils\XmlGenerator;
use PedroACF\Invoicing\Utils\XmlSigner;
use PedroACF\Invoicing\Utils\XmlValidator;

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

    public function addSalePoint(SalePoint $model, SalePointType $salePointType, string $name, string $description): ?SalePoint{
        $request = new RegistroPuntoVentaRequest($model, $salePointType, $name, $description);
        $resp = $this->opeRepo->addSalePoint($request);
        if($resp->transaccion){
            $model = new SalePoint();
            $model->sin_code = $resp->codigoPuntoVenta;
            $model->sale_point_type = $salePointType->codigo_clasificador;
            $model->name = $name;
            $model->description = $description;
            $model->state = 'ACTIVE';
            $model->save();
            return $model;
        }
        return null;
    }

    public function closeSalePoint(SalePoint $salePoint, SalePoint $salePointToClose){
        $request = new CierrePuntoVentaRequest($salePoint, $salePointToClose);
        $response = $this->opeRepo->closeSalePoint($request);
        if($response->transaccion){
            $salePointToClose->state = 'INACTIVE';
            $salePointToClose->save();
        }
        return $salePointToClose;
    }

    public function checkSalePoints(SalePoint $salePoint): ConsultaPuntoVentaResponse{
        $request = new ConsultaPuntoVentaRequest($salePoint);
        $response = $this->opeRepo->checkSalePoints($request);
        foreach($response->salePoints??[] as $rSalePoint){
            $salePoint = SalePoint::where('sin_code', $rSalePoint->codigoPuntoVenta)->first();
            if($salePoint==null){
                $salePoint = new SalePoint();
                $salePoint->sin_code = $rSalePoint->codigoPuntoVenta;
                $salePoint->name = $rSalePoint->nombrePuntoVenta;
                $salePoint->description = '...';
                $type = SalePointType::where("descripcion", $rSalePoint->tipoPuntoVenta)->first();
                if($type!=null){
                    $salePoint->sale_point_type = $type->codigo_clasificador;
                }
            }
            $salePoint->state = "ACTIVE";
            $salePoint->save();
        }
        return $response;
    }

    public function getSignificantEvents(SalePoint $salePoint, Carbon $date): ListaEventosResponse{
        $request = new ConsultaEventoRequest(
            $salePoint,
            $date
        );
        return $this->opeRepo->getSignificantEvents($request);
    }

    public function finishAndSendSignificantEvent($salePoint, SignificantEvent $event, $sales = [], ?string $cafc): ?array{
        $conn = $this->opeRepo->checkConnection();
        if($conn->transaccion){
//            $start = new Carbon($event->start_datetime);
//            $end = new Carbon($event->end_datetime);
            $request = new EventoSignificativoRequest(
                $salePoint,
                $event
            );
            $response = $this->opeRepo->addSignificantEvent($request);
            if($response->transaccion){
                $event->state = 'CLOSED';
                $event->reception_code = $response->codigoRecepcionEventoSignificativo;
                $event->save();
                $this->deleteOldFiles();
                $saleGroups = array_chunk($sales, 500);
                $responseCodes = [];
                foreach($saleGroups as $saleGroup){
                    $now = Carbon::now();
                    $path = public_path('vendor/pacf_invoicing/temp_files/pkg_'.$now->getTimestampMs().'.tar');
                    $packer = new Packer($path);
                    $sale_ids = [];
                    foreach ($saleGroup as $i=> $sale){
                        $sale->refresh();
                        $xmlGenerator = app(XmlGenerator::class);
                        //$xmlGenerator = new XmlGenerator();
                        // COMPLETE INVOICE
                        //$sale->emission_date = $emissionDate;//Formatear
                        $cufd = Cufd::where('code', $event->event_cufd)->first();
                        $sale->cufd = $cufd->code;
                        $sale->sector_doc_type_code = $this->configService->getSectorDocumentCode();
                        $sale->sale_point_code = $salePoint->sin_code;
                        $arrayData = $xmlGenerator->saleToArray(config("pacf_invoicing.main_schema"), $sale, $cufd->codigo_control);
                        $sale->cuf = Arr::get($arrayData, 'head.cuf');
                        $xmlInvoice = $xmlGenerator->arrayToXml($arrayData);
                        $signer = app(XmlSigner::class);
                        $xmlSigned = $signer->sign($xmlInvoice->saveXML());
                        $sale->signed_invoice = $xmlSigned;
                        $sale->save();
                        $sale->refresh();
                        $content = stream_get_contents($sale->signed_invoice);
                        // VALIDAR CON XSD
                        $xmlValidator = new XmlValidator($content);
                        $xmlValidator->validate();
                        //$content = stream_get_contents($sale->signed_invoice);
                        $packer->addFromString("factura_$i.xml", $content);
                        $sale_ids[] = $sale->id;
                    }

                    $package = new Package();
                    $package->sales = implode(',', $sale_ids);
                    $package->save();
                    Sale::whereIn('id', $sale_ids)->update([
                        'package_id' => $package->id,
                    ]);

                    $count = count($saleGroup);
                    $compress = $packer->compress(\Phar::GZ);
                    $file = file_get_contents($path.'.gz');
                    $hash = hash('sha256', $file);
                    $emission = EmissionType::where('descripcion', 'FUERA DE LINEA')->first();
                    $packageRequest = new RecepcionPaqueteFacturaRequest($salePoint, $emission, $file, $hash, $cafc, $count, $event->reception_code);
                    $packageRepo = new PurchaseSaleRepository();
                    $response = $packageRepo->sendInvoicePackage($packageRequest);
                    if($response->transaccion){
                        Sale::whereIn('id', $sale_ids)->update([
                           'reception_code' => $response->codigoRecepcion,
                           'state' => Sale::ENUM_SENT,
                           'significant_event_id' => $event->id
                        ]);
                        $packageIds[] = $package->id;
                        $package->state = Package::ENUM_SENT;
                        $package->reception_code = $response->codigoRecepcion;
                        $package->save();
                    }else{
                        $package->state = Package::ENUM_OBSERVED;
                        $package->message = $response->getJsonMessages();
                        $package->save();
                        $packageIds[] = '0';
                    }
                }
                return $packageIds;
            }else{
                $event->observations = $response->getJsonMessages();
                $event->save();
            }
        }
        return null;
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
