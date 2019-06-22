<?php

namespace App\Service;

use Doctrine\ORM\EntityManagerInterface;

class Market
{
    /**
     * @var EntityManagerInterface
     */
    private $em;

    /**
     * @var Globals
     */
    private $globals;

    /**
     * Market constructor.
     * @param EntityManagerInterface $em
     * @param Globals $globals
     */
    public function __construct(EntityManagerInterface $em, Globals $globals)
    {
        $this->em = $em;
        $this->globals = $globals;
    }

    public function getMarket(): \App\Entity\Building
    {

    }

    public function getTraderNUmberInBase()
    {

    }

    public function testIfEnoughTrader()
    {

    }
}