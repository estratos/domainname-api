<?php

namespace Estratos\DomainNameApi;

use DomainNameApi\DomainNameAPI_PHPLibrary;





class DomainNameAPI
{

    private $username;
    private $password;
    private $test;
    private $dna;

    public function __construct($username = "ownername", $password = "ownerpass", $testMode = false)
    { 
        $this->username = $username;
        $this->password = $password;
        $this->test = $testMode;

    }


    private function init() {
    $this->dna = new DomainNameAPI_PHPLibrary( $this->username. $this->password, $this->test);

    }


    public function GetCurrentBalance($currencyId = 2)
    {
       $this->init();
       return $this->dna->GetCurrentBalance($currencyId);

    }

    public function GetDomainList($extra_parameters = [])
    {
        $this->init();
        return $this->dna->GetList($extra_parameters);

    }

    public function DomainRenew($domainName, $period)
    {
        $this->init();
        return $this->dna->DomainRenew($domainName, $period);

    }



}