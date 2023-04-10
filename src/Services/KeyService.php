<?php

namespace PedroACF\Invoicing\Services;

use PedroACF\Invoicing\Models\SslKey;

class KeyService
{
    public function addPublicKeyFromPem($pemContent){
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

    public function addPublicKeyFromCrt($crtContent){
        $pemFromCrt = '-----BEGIN CERTIFICATE-----'
            .PHP_EOL.chunk_split(base64_encode($crtContent), 64, PHP_EOL)
            .'-----END CERTIFICATE-----'.PHP_EOL;
        $this->addPublicKeyFromPem($pemFromCrt);
    }

    public function addPrivateKeyFromPem($pemContent){
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

    public function addPrivateKeyFromP12($p12Content, $password){
        $status = openssl_pkcs12_read($p12Content, $cert, $password);
        //dd($cert);
        $privateKeyPemContent = (string)$cert['pkey'];
        $this->addPrivateKeyFromPem($privateKeyPemContent);
    }

    public function getAvailablePublicKey(): ?SslKey{
        return SslKey::where('enabled', true)->where('type', SslKey::PUBLIC_KEY)->first();
    }

    public function getPublicKeyPlainText(): ?string{
        $model = $this->getAvailablePublicKey();
        if($model){
            $regexPattern = '/'.'-----BEGIN CERTIFICATE-----'.'(.+)'.'-----END CERTIFICATE-----'.'/Us';
            preg_match($regexPattern, $model, $matches);
            $content = $matches[1];
            $content = str_replace(['\r\n', '\n'], '', $content);
            $content = str_replace('\/', '/', $content);
            return $content;
        }
        return null;
    }

    public function getAvailablePrivateKey(): ?SslKey{
        return SslKey::where('enabled', true)->where('type', SslKey::PRIVATE_KEY)->first();
    }
}
