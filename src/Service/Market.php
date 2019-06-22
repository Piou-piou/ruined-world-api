<?php

namespace App\Service;

class Market
{
    /**
     * @var Globals
     */
    private $globals;

    /**
     * Market constructor.
     * @param Globals $globals
     */
    public function __construct(Globals $globals)
    {
        $this->globals = $globals;
    }

    public function testIfEnoughTrader()
    {

    }
}