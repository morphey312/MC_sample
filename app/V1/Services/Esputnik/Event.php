<?php


namespace App\V1\Services\Esputnik;

use App\V1\Contracts\Services\Esputnik\Event as Contract;
use App\V1\Contracts\Services\Esputnik\Sender;
use App\V1\Traits\Esputnik\SendsRequest;


class Event implements Contract
{
    use SendsRequest;

    const ENDPOINT = 'event';
    const METHOD = 'post';

    protected $requestData;

    protected $sender;

    public function __construct(Sender $sender)
    {
        $this->sender = $sender;
    }

    public function setType(string $type): Contract
    {
        $this->requestData['eventTypeKey'] = $type;

        return $this;
    }

    public function setTarget(string $target): Contract
    {
        $this->requestData['keyValue'] = $target;

        return $this;
    }

    public function setData(array $data): Contract
    {
        $this->requestData['params'] = $data;

        return $this;
    }

    public function addParam(string $name, $value): Contract
    {
        $params = collect($this->requestData['params'] ?? [])
            ->filter(function ($param) use ($name) {
                return $param['name'] !== $name;
            })->push([
                'name' => $name,
                'value' => $value
            ]);;

        $this->requestData['params'] = $params->toArray();

        return $this;
    }

    public function toArray()
    {
        return $this->requestData;
    }
}
