<?php

namespace App\V1\Traits\Repositories\Query;

use DB;

trait MultiReplace
{
    /**
     * Make nested replace
     * 
     * @param string $column
     * @param array $what
     * @param string|null $for
     */ 
    public function makeMultireplaceFunction($column, $what, $for = null)
    {
        $pdo = DB::connection()->getPdo();
        $result = $column;
        foreach ($what as $key => $value) {
            if ($for === null) {
                $srchStr = $key;
                $replStr = $value;
            } else {
                $srchStr = $value;
                $replStr = $for;
            }
            $result = sprintf(
                'REPLACE(%s, %s, %s)', 
                $result, $pdo->quote($srchStr), $pdo->quote($replStr)
            );
        }
        return $result;
    }
    
    /**
     * Remove non-letter chars from string
     * 
     * @param string $column
     * 
     * @return string
     */ 
    public function removeNonLetterCharacters($column)
    {
        return $this->makeMultireplaceFunction($column, [
            '~', '`', '!', '@', '#', '$', '%', '^', '&', '*', '(', ')', '-', '_', '+',
            '=', '{', '[', '}', ']', '|', '\\', '//', ',', '.', ':', ';', '"', '\'', '?', '>', '<',
            '    ', '   ', '  ',
        ], ' ');
    }
}