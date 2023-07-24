<?php

namespace PedroACF\Invoicing\Services;

use Illuminate\Support\Arr;
use PedroACF\Invoicing\Invoices\EInvoice;
use PedroACF\Invoicing\Models\SIN\CancelReason;
use PedroACF\Invoicing\Models\SIN\EmissionType;
use PedroACF\Invoicing\Models\SYS\Invoice;
use PedroACF\Invoicing\Models\SYS\Package;
use PedroACF\Invoicing\Models\SYS\Sale;
use PedroACF\Invoicing\Models\SYS\SalePoint;
use PedroACF\Invoicing\Repositories\PurchaseSaleRepository;
use PedroACF\Invoicing\Requests\PurchaseSale\AnulacionFacturaRequest;
use PedroACF\Invoicing\Requests\PurchaseSale\RecepcionFacturaRequest;
use PedroACF\Invoicing\Requests\PurchaseSale\ValidacionRecepcionPaqueteRequest;
use PedroACF\Invoicing\Requests\PurchaseSale\VerificacionEstadoFacturaRequest;
use PedroACF\Invoicing\Responses\PurchaseSale\ServicioFacturacionResponse;
use PedroACF\Invoicing\Utils\XmlGenerator;
use PedroACF\Invoicing\Utils\XmlSigner;
use PedroACF\Invoicing\Utils\XmlValidator;
use splitbrain\PHPArchive\Archive;
use splitbrain\PHPArchive\Zip;

class InvoicingService
{
    private $psRepo;
    private $codeService;
    private $configService;

    public function __construct(PurchaseSaleRepository $psRepo, ConfigService $configService, CodeService $codeService)
    {
        $this->psRepo = $psRepo;
        $this->codeService = $codeService;
        $this->configService = $configService;
    }
    public function sendElectronicInvoice(SalePoint $salePoint, Sale $sale): bool{
        $conn = $this->psRepo->checkConnection();
        if($conn->transaccion){
            $sale->refresh();
            $xmlGenerator = app(XmlGenerator::class);
            //$xmlGenerator = new XmlGenerator();
            $emissionDate = $this->configService->getTime();
            // COMPLETE INVOICE
            $cufd = $salePoint->cufdCodes()->where('state','ACTIVE')->first();
            $sale->emission_date = $emissionDate;//Formatear
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
            // dd(libxml_get_errors());
            // COMPRIMIR ZIP
            $compressed = gzencode($content);
            //$base64 = base64_encode($compressed);
            // OBTENER HASH
            $hash = hash('sha256', $compressed);
            //SEND PACKAGE
            $request = new RecepcionFacturaRequest(
                $salePoint,
                $sale->emission_type_code,
                $this->configService->getInvoiceTypeCode(),
                $compressed,
                $hash
            );
            $response = $this->psRepo->sendInvoice($request);
            if($response->transaccion){
                $sale->reception_code = $response->codigoRecepcion;
                $sale->state = Sale::ENUM_VALID;
                $sale->response_code = $response->codigoEstado;
                $sale->observations = $response->getJsonMessages();
                $sale->save();
                return true;
            }else{
                $sale->state = Sale::ENUM_REJECTED;
                $sale->response_code = $response->codigoEstado;
                $sale->observations = $response->getJsonMessages();
                $sale->save();
            }
        }
        return false;
    }

    public function cancelInvoice(SalePoint $salePoint, Sale $sale, CancelReason $reason): ServicioFacturacionResponse{
        $emission = EmissionType::where('descripcion', 'EN LINEA')->first();
        $sectorDocumentCode = $this->configService->getSectorDocumentCode();
        $request = new AnulacionFacturaRequest(
            $salePoint,
            $sectorDocumentCode,
            $emission->codigo_clasificador,
            $this->configService->getInvoiceTypeCode(),
            $reason->codigo_clasificador,
            $sale->cuf
        );
        $result = $this->psRepo->cancelInvoice($request);
        if($result->transaccion){
            $sale->cancel_code = $reason->codigo_clasificador;
            $sale->cancel_reason = $reason->descripcion;
            $sale->save();
        }
        dump("cancel");
        dump($result);
        return $result;
    }

    public function validatePackageReception(SalePoint $salePoint, Package $package){
        $request = new ValidacionRecepcionPaqueteRequest($salePoint, $this->configService->getInvoiceTypeCode(), $package->reception_code);//internamente ya esta emission offline
        $response = $this->psRepo->validateInvoicePackageSend($request);
        $sales = explode(',', $package->sales);
        if($response->transaccion && $response->codigoDescripcion == 'VALIDADA'){
            $package->state = Package::ENUM_VALID;
            $package->response_code = $response->codigoEstado;
            $package->messages = $response->getJsonMessages();
            $package->save();
            Sale::whereIn('id', $sales)->update([
                'state' => Sale::ENUM_VALID
            ]);
        }elseif($response->codigoDescripcion='OBSERVADA'){
            $package->state = Package::ENUM_OBSERVED;
            $package->response_code = $response->codigoEstado;
            $package->messages = $response->getJsonMessages();
            $package->save();
            Sale::whereIn('id', $sales)->update([
                'state' => Sale::ENUM_OBSERVED
            ]);
        }
        dump("validate on invicing service");
        dump($response);
    }

    public function checkInvoiceStatus(Invoice $invoice){
        $request = new VerificacionEstadoFacturaRequest(1, 1, 1, $invoice->cuf);
        $result = $this->psRepo->checkInvoiceStatus($request);
        dd($result);
    }
}
