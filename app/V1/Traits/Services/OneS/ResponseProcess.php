<?php

namespace App\V1\Traits\Services\OneS;

trait ResponseProcess
{
    /**
     * \GuzzleHttp\Client
     */
    protected $client;

    /**
     * @var success status code
     */ 
    protected $codeSuccess = 200;
    
    /**
     * Get decoded response body
     * 
     * @param Http response $response
     * 
     * @return array
     */ 
    protected function getDecodedBody($response)
    {
        return json_decode($this->getBody($response), true);
    }

    /**
     * Get BOM free response body
     * 
     * @param object $response
     * 
     * @return string
     */ 
    protected function getBody($response)
    {
        $body = $response->getBody();
        $bom = pack("CCC", 0xef, 0xbb, 0xbf);
        if (0 === strncmp($body, $bom, 3)) {
            $body = substr($body, 3);
        }

        return $body;
    }
} 