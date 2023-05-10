<?php

namespace PedroACF\Invoicing\Services;

use PedroACF\Invoicing\Exceptions\KeyException;
use PedroACF\Invoicing\Models\SYS\SslKey;

class KeyService
{
    private $privateKeyContent;
    private $publicKeyContent;
    public function addPublicKeyFromPem($pemContent){
        SslKey::where('type', SslKey::PUBLIC_KEY)->update([
            'enabled' => false
        ]);
        $keyModel = new SslKey();
        $keyModel->content = $pemContent;
        $keyModel->type = SslKey::PUBLIC_KEY;
        $keyModel->enabled = true;
        $keyModel->save();
        $this->publicKeyContent = null;
    }

    public function addPrivateKeyFromPem($pemContent){
        //disable all private Key
        SslKey::where('type', SslKey::PRIVATE_KEY)->update([
            'enabled' => false
        ]);
        $keyModel = new SslKey();
        $keyModel->content = $pemContent;
        $keyModel->type = SslKey::PRIVATE_KEY;
        $keyModel->enabled = true;
        $keyModel->save();
        $this->privateKeyContent = null;
    }

    public function getPublicKeyModel(): ?SslKey{
        return SslKey::where('enabled', true)->where('type', SslKey::PUBLIC_KEY)->first();
    }

    public function getPublicKeyContent(): string{
        if($this->publicKeyContent==null){
            $publicModel = $this->getPublicKeyModel();
            if(!$publicModel){
                throw new KeyException();
            }
            $this->publicKeyContent = stream_get_contents($publicModel->content);
        }
        return $this->publicKeyContent;
    }

    public function getPrivateKeyModel(): ?SslKey{
        return SslKey::where('enabled', true)->where('type', SslKey::PRIVATE_KEY)->first();
    }

    public function getPrivateKeyContent(): string{
        if($this->privateKeyContent==null){
            $privateModel = $this->getPrivateKeyModel();
            if(!$privateModel){
                throw new KeyException();
            }
            $this->privateKeyContent = stream_get_contents($privateModel->content);
        }
        return $this->privateKeyContent;
    }
}
