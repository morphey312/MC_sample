<?php

namespace App\V1\Services\Voip;

use App\V1\Contracts\Services\Voip\SubResolver as SubResolverInterface;
use App\V1\Contracts\Repositories\EmployeeRepository;
use App\V1\Contracts\Repositories\PatientRepository;

class SubResolver implements SubResolverInterface
{
    const INTERNAL_NUMBER_REGEXP = '/^[0-9]{3,4}$/';
    
    /**
     * @var EmployeeRepository
     */
    protected $employees;
    
    /**
     * @var PatientRepository
     */
    protected $patients;
    
    /**
     * Constructor
     * 
     * @param EmployeeRepository $employees
     * @param  PatientRepository $patients
     */ 
    public function __construct(EmployeeRepository $employees, PatientRepository $patients)
    {
        $this->employees = $employees;
        $this->patients = $patients;
    }
    
    /**
     * @inheritdoc
     */ 
    public function resolve($sub)
    {
        list($scheme, $number, $host) = $this->parseSub($sub);
        
        if ($this->isInternalNumber($number)) {
            $subject = $this->employees->findBySipNumber($number, false);
        } else {
            $subject = $this->patients->findByPhoneNumber($number, false);
            if ($subject === null) {
                $subject = $this->employees->findByPhoneNumber($number, false);
            }
        }
        
        return [
            'scheme' => $scheme,
            'number' => $number,
            'via' => $host,
            'subject' => $subject,
        ];
    }
    
    /**
     * @inheritdoc
     */ 
    public function resolveAll($sub)
    {
        list($scheme, $number, $host) = $this->parseSub($sub);
        
        $subjects = collect([]);
        if ($this->isInternalNumber($number)) {
            foreach ($this->employees->findAllBySipNumber($number, false) as $employee) {
                $subjects->push($employee);
            }
        } else {
            foreach ($this->patients->findAllByPhoneNumber($number, false) as $patient) {
                $subjects->push($patient);
            }
            foreach ($this->employees->findAllByPhoneNumber($number, false) as $employee) {
                $subjects->push($employee);
            }
        }
        
        return [
            'scheme' => $scheme,
            'number' => $number,
            'via' => $host,
            'subjects' => $subjects,
        ];
    }
    
    /**
     * Parse sub address like sip:123@host
     * 
     * @param string $sub
     * 
     * @return array
     */ 
    protected function parseSub($sub)
    {
        $scheme = null;
        $host = null;
        if (strpos($sub, ':') !== false) {
            list($scheme, $sub) = explode(':', $sub, 2);
        }
        if (strpos($sub, '@') !== false) {
            list($sub, $host) = explode('@', $sub, 2);
        }
        return [$scheme, $sub, $host];
    }
    
    /**
     * Check if the given number is internal one
     * 
     * @param string $number
     * 
     * @return bool
     */ 
    protected function isInternalNumber($number)
    {
        return preg_match(self::INTERNAL_NUMBER_REGEXP, $number);
    }
}