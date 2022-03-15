<?php


namespace App\V1\Contracts\Services\Esputnik;


interface Event
{
    public function setType(string $type): Event;

    public function setTarget(string $target): Event;

    public function setData(array $data): Event;

    public function addParam(string $name, $value): Event;
}
