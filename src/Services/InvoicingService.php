<?php

namespace PedroACF\Invoicing\Services;

use PedroACF\Invoicing\Models\CancelReason;
use PedroACF\Invoicing\Models\Invoice;
use PedroACF\Invoicing\Repositories\PurchaseSaleRepository;
use PedroACF\Invoicing\Requests\PurchaseSale\AnulacionFacturaRequest;
use PedroACF\Invoicing\Requests\PurchaseSale\RecepcionFacturaRequest;

class InvoicingService
{
    private $repo;

    public function __construct()
    {
        $this->repo = new PurchaseSaleRepository();
    }
    public function sendInvoice(RecepcionFacturaRequest $req){
        $result = $this->repo->sendInvoice($req);
    }

    public function cancelInvoice(Invoice $invoice, CancelReason $reason){
        $sectorDocumentCode = 1; // TODO: REVISAR
        $emissionCode = 1;//TODO: Mejorar
        $codeService = new CodeService();
        $cuisModel = $codeService->getValidCuisModel();//TODO: Mejorar
        $cufdModel = $codeService->getValidCufdModel();//TODO: Mejorar
        $request = new AnulacionFacturaRequest(
            $sectorDocumentCode,
            $emissionCode,
            $cufdModel->cufd,
            $cuisModel->cuis,1,
            $reason->codigo_clasificador,
            $invoice->cuf
        );
        $result = $this->repo->cancelInvoice($request);
        dump($result);
    }
}
