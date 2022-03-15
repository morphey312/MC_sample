<?php

namespace App\V1\Contracts;

interface Multilingual
{
    /**
     * Get current locale alias
     * 
     * @return string
     */
    public function getLocaleSuffix();

    /**
     * Get list of known locales
     * 
     * @return array
     */
    public function getKnownLocales();
}