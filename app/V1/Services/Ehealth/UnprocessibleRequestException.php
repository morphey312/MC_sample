<?php

namespace App\V1\Services\Ehealth;

use Exception;

class UnprocessibleRequestException extends Exception
{
    /**
     * @var array
     */
    protected $response;

    /**
     * Constructor
     * 
     * @param array $response
     * @param int $code
     */
    public function __construct($response, $code)
    {
        parent::__construct('Unexpected HTTP status code', $code);
        $this->response = $response;
    }

    /**
     * Get response
     * 
     * @return array
     */
    public function getResponse()
    {
        return $this->response;
    }
}