<?php
namespace PedroACF\Invoicing\Invoices;

use Brick\Math\BigInteger;
use PedroACF\Invoicing\Models\SIN\Cufd;
use PedroACF\Invoicing\Services\ConfigService;

class HeaderEInvoice extends BaseHeaderInvoice
{
    public $nitEmisor; //1003579028
    public $razonSocialEmisor; //Carlos Loza
    public $municipio; //La Paz
    public $telefono; //2846005
    public $numeroFactura; //1
    public $cuf; //44AAEC00DBD34C819B4D7AFD5F91900D3A059E06A467A75AC82F24C74
    public $cufd; //BQUE+QytqQUDBKVUFOSVRPQkxVRFZNVFVJBMDAwMDAwM
    public $codigoSucursal; //0
    public $direccion; //AV. JORGE LOPEZ #123
    public $codigoPuntoVenta; //nullable
    public $fechaEmision; //2021-10-07T09:01:24.178
    public $nombreRazonSocial; //Mi razon social
    public $codigoTipoDocumentoIdentidad; //1
    public $numeroDocumento; //5115889
    public $complemento; //nullable
    public $codigoCliente; //51158891
    public $codigoMetodoPago; //1
    public $numeroTarjeta; //nullable
    public $montoTotal; //99
    public $montoTotalSujetoIva; //99
    public $codigoMoneda; //1
    public $tipoCambio; //1
    public $montoTotalMoneda; //99
    public $montoGiftCard; //nullable
    public $descuentoAdicional; //1
    public $codigoExcepcion; //nullable
    public $cafc; //nullable
    public $leyenda; //Ley N° 453: Tienes derecho a recibir información sobre las características y contenidos ...
    public $usuario; //pperez
    public $codigoDocumentoSector; //1

    public function generateCufCode(Cufd $cufdModel){
        $config = ConfigService::getConfigs();

        $nit = str_pad($this->nitEmisor, 13, "0", STR_PAD_LEFT);
        $date = str_replace(["-","T",":","."], "", $this->fechaEmision);
        $office = str_pad($this->codigoSucursal, 4, "0", STR_PAD_LEFT);
        $mode = 1;//config("siat_invoicing.mode");
        $emissionType = 1;//1=>online, 2=> offline, 3=>masiva ->SACAR DE LA BD
        $invoiceType = 1;//SACAR DE LA BD
        $sectorType = str_pad(1, 2, "0", STR_PAD_LEFT);//SACAR DE LA BD
        $invoiceNumber = str_pad($this->numeroFactura, 10, "0", STR_PAD_LEFT);
        $salePoint = str_pad($config->sale_point, 4, "0", STR_PAD_LEFT);

        $cuf = $nit.$date.$office.$mode.$emissionType.$invoiceType.$sectorType.$invoiceNumber.$salePoint;
        $number = $this->mod11String($cuf);
        $number = BigInteger::of($number);
        $cuf = $number->toBase(16);
        $this->cuf = strtoupper($cuf).$cufdModel->codigo_control;
    }

    private function mod11String(string $number){
        //int mult, suma, i, n, dig;
        $limMul = 9;
        $sum = 0;
        $mul = 2;
        for($i = strlen($number) - 1; $i >= 0; $i--){
            $sum += ($mul * ((int)$number[$i]));
            if(++$mul > $limMul){
                $mul = 2;
            }
        }
        $mod = $sum % 11;

        //TODO: Verificar esto, falta resta segun algoritmo

        if ($mod == 10) {
            $number .= "1";
        }

        if ($mod == 11) {
            $number .= "0";
        }
        if ($mod < 10) {
            $number .= $mod;
        }
        return $number;
    }
}
