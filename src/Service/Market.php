<?php

namespace App\Service;

use App\Entity\Base;
use App\Entity\MarketMovement;
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
     * @var Base
     */
    private $base;

    /**
     * @var Building
     */
    private $building;

    /**
     * @var \App\Entity\Building
     */
    private $market;

    /**
     * Market constructor.
     * @param EntityManagerInterface $em
     * @param Globals $globals
     * @param Building $building
     */
    public function __construct(EntityManagerInterface $em, Globals $globals, Building $building)
    {
        $this->em = $em;
        $this->globals = $globals;
        $this->base = $globals->getCurrentBase();
        $this->building = $building;
        $this->market = $this->getMarket();
    }

    /**
     * method to get market of the base
     * @return \App\Entity\Building
     */
    private function getMarket(): \App\Entity\Building
    {
        $market = $this->em->getRepository(\App\Entity\Building::class)->findOneBy([
            "base" => $this->base,
            "array_name" => "market"
        ]);

        return $market;
    }

    /**
     * method that return number of traders in base
     * @return int
     */
    public function getTraderNumberInBase(): int
    {
        $trader_max = $this->building->getCurrentPower($this->market->getArrayName(), $this->market->getLevel());
        $trader_inmove = $this->em->getRepository(MarketMovement::class)->findByTraderInMove($this->base);

        return $trader_max - $trader_inmove;
    }

    public function testIfEnoughTrader()
    {

    }
}