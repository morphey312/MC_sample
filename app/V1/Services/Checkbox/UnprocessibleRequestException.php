<?php

namespace App\V1\Services\Checkbox;

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
        $message = $response ?? 'Unexpected HTTP status code';
        parent::__construct($message, $code);
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
