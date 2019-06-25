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
	 * @var Resources
	 */
    private $resources;

    /**
     * @var \App\Entity\Building
     */
    private $market;

	/**
	 * Market constructor.
	 * @param EntityManagerInterface $em
	 * @param Globals $globals
	 * @param Building $building
	 * @param Resources $resources
	 */
    public function __construct(EntityManagerInterface $em, Globals $globals, Building $building, Resources $resources)
    {
        $this->em = $em;
        $this->globals = $globals;
        $this->base = $globals->getCurrentBase();
        $this->building = $building;
        $this->resources = $resources;
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

	/**
	 * method to updateMarketMovement of the base
	 * @param Base $base
	 * @throws \Exception
	 */
    public function updateMarketMovement(Base $base)
	{
    	$em = $this->em;
    	$market_movements_ended = $em->getRepository(MarketMovement::class)->findByMovementEnded($base);

    	foreach ($market_movements_ended as $market_movement) {
    		if ($market_movement->getType() === MarketMovement::TYPE_GO) {
    			$this->updateMarketMovementOnGo($market_movement);
			}
		}
	}

	/**
	 * method to update ended market that are on the go
	 * @param MarketMovement $market_movement
	 * @throws \Exception
	 */
	private function updateMarketMovementOnGo(MarketMovement $market_movement)
	{
		$end_date = new \DateTime();
		$end_date->add(new \DateInterval("PT".$market_movement->getDuration()."S"));
		$base_dest = $market_movement->getBaseDest();
		$this->resources->setBase($base_dest);

		foreach ($market_movement->getResources() as $resource_name => $resource_value) {
			$this->resources->addResource($resource_name, $resource_value);
		}
		$this->resources->setBase(null);

		$market_movement->setType(MarketMovement::TYPE_RETURN);
		$market_movement->setEndDate($end_date);
		$this->em->persist($market_movement);
		$this->em->flush();
	}
}