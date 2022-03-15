<?php

namespace App\V1\Traits;

trait PhoneNumber
{
    /**
     * @var array
     */
    protected $transformRules = [
        '/^([+]?([0-9]{10,14}))$/' => 1, // Phone number
        '/^([0-9]{3,5})$/' => 1, // Sip numbers
        '/^[+]?([0-9]{6,})$/' => 1, // Unsorted
    ];

    /**
     * Normalize phone number
     *
     * @param string $number
     *
     * @return string
     */
    public function normalizePhoneNumber($number)
    {
        foreach ($this->transformRules as $pattern => $index) {
            if (preg_match($pattern, $number, $matches)) {
                return $matches[$index];
            }
        }
        return null;
    }
}
