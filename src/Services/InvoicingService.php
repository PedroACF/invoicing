<?php

namespace PedroACF\Invoicing\Services;

use PedroACF\Invoicing\Invoices\EInvoice;
use PedroACF\Invoicing\Models\SIN\CancelReason;
use PedroACF\Invoicing\Models\SYS\Invoice;
use PedroACF\Invoicing\Models\SYS\Sale;
use PedroACF\Invoicing\Models\SYS\SalePoint;
use PedroACF\Invoicing\Repositories\PurchaseSaleRepository;
use PedroACF\Invoicing\Requests\PurchaseSale\AnulacionFacturaRequest;
use PedroACF\Invoicing\Requests\PurchaseSale\RecepcionFacturaRequest;
use PedroACF\Invoicing\Requests\PurchaseSale\ValidacionRecepcionPaqueteRequest;
use PedroACF\Invoicing\Requests\PurchaseSale\VerificacionEstadoFacturaRequest;
use PedroACF\Invoicing\Responses\PurchaseSale\ServicioFacturacionResponse;
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
            $emissionDate = $this->configService->getTime();
            // COMPLETE INVOICE
            $cufd = $salePoint->cufdCodes()->where('state','ACTIVE')->first();
            $sale->emission_date = $emissionDate;//Formatear
            //$invoice->header->nitEmisor = $this->configService->getNit();
            //$invoice->header->razonSocialEmisor = $this->configService->getBusinessName();
            //$invoice->header->municipio = $this->configService->getMunicipality();
            //$invoice->header->telefono = $this->configService->getOfficePhone();
            $sale->cufd = $cufd->code;
            $sale->sector_doc_type_code = $this->configService->getSectorDocumentCode();
            $sale->sale_point_code = $salePoint->sin_code;
            $invoice->header->generateCufCode($salePoint, $cufd);

            // FIRMAR XML
            $signer = app(XmlSigner::class);
            $signedXML = $signer->sign($invoice->toXml()->saveXML());

            // SAVE MODEL INVOICE
            $document = [$invoice->header->numeroDocumento];
            if(isset($invoice->header->complemento)){
                $document[] = $invoice->header->complemento;
            }
            $model = new Invoice();
            $model->number = $invoiceNumber;
            $model->cuf = $invoice->header->cuf;
            $model->document = implode("-", $document);
            $model->client_name = $invoice->header->razonSocialEmisor;
            $model->emission_date = $emissionDate;
            $model->amount = $invoice->header->montoTotal;
            $model->content = $signedXML;
            $model->user_id = 0;//$faker->randomNumber();
            $model->save();
            $model->refresh();
            $content = stream_get_contents($model->content);

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
                $emissionType,
                $invoiceType,
                $compressed,
                $hash
            );
            $response = $this->psRepo->sendInvoice($request);
            if($response->transaccion){
                return true;
            }
        }
        return false;
    }

    public function cancelInvoice(SalePoint $salePoint, Invoice $invoice, CancelReason $reason, $emissionCode): ServicioFacturacionResponse{
        $sectorDocumentCode = $this->configService->getSectorDocumentCode();
        $request = new AnulacionFacturaRequest(
            $salePoint,
            $sectorDocumentCode,
            $emissionCode,1,
            $reason->codigo_clasificador,
            $invoice->cuf
        );
        $result = $this->psRepo->cancelInvoice($request);
        dump($result);
        return $result;
    }

    public function validatePackageReception(SalePoint $salePoint, $receptionCode){
        $request = new ValidacionRecepcionPaqueteRequest($salePoint, 2, 1, $receptionCode);
        $response = $this->psRepo->validateInvoicePackageSend($request);
        dump($response);
    }

    public function checkInvoiceStatus(Invoice $invoice){
        $request = new VerificacionEstadoFacturaRequest(1, 1, 1, $invoice->cuf);
        $result = $this->psRepo->checkInvoiceStatus($request);
        dd($result);
    }
}
