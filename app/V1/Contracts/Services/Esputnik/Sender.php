<?php


namespace App\V1\Contracts\Services\Esputnik;

interface Sender
{
    /**
     * Process eSputnik request
     *
     * @param $method
     * @param $endpoint
     * @param $data
     * @return mixed
     */
    public function request($method, $endpoint, $data) : bool;
}
