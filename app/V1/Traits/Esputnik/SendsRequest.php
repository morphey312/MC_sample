<?php


namespace App\V1\Traits\Esputnik;


use App\V1\Contracts\Services\Esputnik\Sender;

trait SendsRequest
{
    public static function bootSendsRequest()
    {
        if(!defined(static::class . '::ENDPOINT') || !defined(static::class . '::METHOD')) {
            throw new \Exception('Class must contain "ENDPOINT" and "METHOD" constants');
        }
    }

    public function send(): bool
    {
        return $this->sender->request(static::METHOD, static::ENDPOINT, $this->requestData);
    }
}
