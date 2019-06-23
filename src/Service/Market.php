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

    /**
     * get trader number for resources in parameter
     * @param array $resources
     * @return float
     */
    public function getTraderToTransport(array $resources): float
    {
        $resources_per_trader = $this->globals->getBuildingsConfig()["market"]["resources_per_trader"];
        $resources_to_move = 0;

        foreach ($resources as $resource) {
            $resources_to_move += $resource;
        }

        return ceil($resources_to_move / $resources_per_trader);
    }

    /**
     * method to test if base has enough trader to transport resources
     * @param array $resources
     * @return bool
     */
    public function testIfEnoughTrader(array $resources): bool
    {
        $trader_to_transport = $this->getTraderToTransport($resources);
        if ($trader_to_transport > $this->getTraderNumberInBase()) {
            return false;
        }

        return true;
    }
}