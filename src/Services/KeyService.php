<?php

namespace PedroACF\Invoicing\Services;

use PedroACF\Invoicing\Models\SslKey;

class KeyService
{
    public static function addPublicKeyFromPem($pemContent){
        //disable all public Key
        SslKey::where('type', SslKey::PUBLIC_KEY)->update([
            'enabled' => false
        ]);
        $newKey = new SslKey();
        $newKey->content = $pemContent;
        $newKey->type = SslKey::PUBLIC_KEY;
        $newKey->enabled = true;
        $newKey->save();
    }

    public static function addPublicKeyFromCrt($crtContent){
        $pemFromCrt = '-----BEGIN CERTIFICATE-----'
            .PHP_EOL.chunk_split(base64_encode($crtContent), 64, PHP_EOL)
            .'-----END CERTIFICATE-----'.PHP_EOL;
        KeyService::addPublicKeyFromPem($pemFromCrt);
    }

    public static function addPrivateKeyFromPem($pemContent){
        //disable all private Key
        SslKey::where('type', SslKey::PRIVATE_KEY)->update([
            'enabled' => false
        ]);
        $newKey = new SslKey();
        $newKey->content = $pemContent;
        $newKey->type = SslKey::PRIVATE_KEY;
        $newKey->enabled = true;
        $newKey->save();
    }

    public static function addPrivateKeyFromP12($p12Content, $password){
        $status = openssl_pkcs12_read($p12Content, $cert, $password);
        //dd($cert);
        $privateKeyPemContent = (string)$cert['pkey'];
        KeyService::addPrivateKeyFromPem($privateKeyPemContent);
    }

    public static function getAvailablePublicKey(): ?SslKey{
        return SslKey::where('enabled', true)->where('type', SslKey::PUBLIC_KEY)->first();
    }

    public static function getPublicCert(): ?string{
        $model = KeyService::getAvailablePublicKey();
        if($model){
            $cert = stream_get_contents($model->content);
            return $cert;
        }
        return null;
    }

    public static function getAvailablePrivateKey(): ?SslKey{
        return SslKey::where('enabled', true)->where('type', SslKey::PRIVATE_KEY)->first();
    }
}
