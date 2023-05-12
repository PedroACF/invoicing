<?php

namespace PedroACF\Invoicing\Services;

use PedroACF\Invoicing\Invoices\EInvoice;
use PedroACF\Invoicing\Models\SIN\CancelReason;
use PedroACF\Invoicing\Models\SYS\Invoice;
use PedroACF\Invoicing\Repositories\PurchaseSaleRepository;
use PedroACF\Invoicing\Requests\PurchaseSale\AnulacionFacturaRequest;
use PedroACF\Invoicing\Requests\PurchaseSale\RecepcionFacturaRequest;
use PedroACF\Invoicing\Requests\PurchaseSale\VerificacionEstadoFacturaRequest;
use PedroACF\Invoicing\Utils\XmlSigner;
use PedroACF\Invoicing\Utils\XmlValidator;

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
    public function sendElectronicInvoice(int $salePoint, EInvoice $invoice, int $emissionType, int $invoiceType): bool{
        $conn = $this->psRepo->checkConnection();
        if($conn->transaccion){
            $emissionDate = $this->configService->getTime();
            // COMPLETE INVOICE
            $cufdModel = $this->codeService->getValidCufdModel($salePoint);
            $cuisModel = $this->codeService->getValidCuisModel($salePoint);
            $invoiceNumber = $this->configService->getAvailableInvoiceNumber();
            $invoice->header->fechaEmision = $emissionDate->format("Y-m-d\TH:i:s.v");
            $invoice->header->nitEmisor = $this->configService->getNit();
            $invoice->header->razonSocialEmisor = $this->configService->getBusinessName();
            $invoice->header->municipio = $this->configService->getMunicipality();
            $invoice->header->telefono = $this->configService->getOfficePhone();
            $invoice->header->numeroFactura = $invoiceNumber;
            $invoice->header->cufd = $cufdModel->cufd;
            $invoice->header->codigoSucursal = $this->configService->getOfficeCode();
            $invoice->header->direccion = $this->configService->getOfficeAddress();
            $invoice->header->codigoDocumentoSector = $this->configService->getSectorDocumentCode();
            $invoice->header->generateCufCode($cufdModel, $salePoint);

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
            // OBTENER HASH
            $hash = hash('sha256', $compressed);
            //SEND PACKAGE
            $request = new RecepcionFacturaRequest(
                $salePoint,
                $emissionType,
                $cufdModel->cufd,
                $cuisModel->cuis,
                $invoiceType,
                $compressed,
                $hash
            );
            $response = $this->psRepo->sendInvoice($request);
            if($response->transaccion){
                //Hacer algo con el envio
            }
            return true;
        }
        return false;
    }

    public function cancelInvoice($salePoint, Invoice $invoice, CancelReason $reason, $emissionCode){
        $sectorDocumentCode = $this->configService->getSectorDocumentCode();
        $cuisModel = $this->codeService->getValidCuisModel($salePoint);//TODO: Mejorar
        $cufdModel = $this->codeService->getValidCufdModel($salePoint);//TODO: Mejorar
        $request = new AnulacionFacturaRequest(
            $salePoint,
            $sectorDocumentCode,
            $emissionCode,
            $cufdModel->cufd,
            $cuisModel->cuis,1,
            $reason->codigo_clasificador,
            $invoice->cuf
        );
        $result = $this->psRepo->cancelInvoice($request);
        dump($result);
        return true;//TODO: Mejorar mas
    }

    public function checkInvoiceStatus(Invoice $invoice){
        $request = new VerificacionEstadoFacturaRequest(1, 1, 1, $invoice->cuf);
        $result = $this->psRepo->checkInvoiceStatus($request);
        dd($result);
    }
}
