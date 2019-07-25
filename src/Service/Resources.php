<?php

namespace App\Service;

use App\Entity\Base;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class Resources
{
	/**
	 * @var EntityManagerInterface
	 */
	private $em;
	
	/**
	 * @var SessionInterface
	 */
	private $session;
	
	/**
	 * @var Globals
	 */
	private $globals;
	
	/**
	 * @var Base
	 */
	private $base;

	/**
	 * @var Base
	 */
	private $other_base;
	
	/**
	 * @var int
	 */
	private $max_warehouse_storage;
	
	/**
	 * @var int
	 */
	private $max_garner_storage;
	
	/**
	 * @var int
	 */
	private $electricity_production;
	
	/**
	 * @var int
	 */
	private $fuel_production;
	
	/**
	 * @var int
	 */
	private $iron_production;
	
	/**
	 * @var int
	 */
	private $water_production;

	/**
	 * Resources constructor.
	 * @param EntityManagerInterface $em
	 * @param SessionInterface $session
	 * @param Globals $globals
	 */
	public function __construct(EntityManagerInterface $em, SessionInterface $session, Globals $globals)
	{
		$this->em = $em;
		$this->session = $session;
		$this->globals = $globals;
		$this->base = $globals->getCurrentBase(true);
	}

	/**
	 * method to get current base used in this service
	 * @return Base
	 */
	private function getBase(): Base
	{
		if (!$this->other_base) {
			return $this->base;
		}
		return $this->other_base;
	}

	/**
	 * method to use toher base in this service
	 * @param Base|null $other_base
	 */
	public function setBase($other_base)
	{
		$this->other_base = $other_base;
	}
	
	/**
	 * method called to add resource
	 * @param string $resource
	 * @param int $value_to_add
	 */
	public function addResource(string $resource, int $value_to_add)
	{
		$getter = "get" . ucfirst($resource);
		$setter = "set" . ucfirst($resource);
		
		$new_resource = $this->getBase()->$getter() + $value_to_add;
		
		if ($resource !== "food" && $new_resource > $this->getWarehouseCapacity()) {
			$new_resource = $this->getWarehouseCapacity();
		} else if ($resource === "food" && $new_resource > $this->getGarnerCapacity()) {
			$new_resource = $this->getGarnerCapacity();
		}
		
		$this->getBase()->$setter($new_resource);
		$this->em->flush();
	}
	
	/**
	 * method called to withdraw resource
	 * @param string $resource
	 * @param int $value_to_del
	 */
	public function withdrawResource(string $resource, int $value_to_del)
	{
		$getter = "get" . ucfirst($resource);
		$setter = "set" . ucfirst($resource);
		
		$new_resource = $this->getBase()->$getter() - $value_to_del;
		
		if ($new_resource < 0) {
			$new_resource = 0;
		}
		
		$this->getBase()->$setter($new_resource);
		$this->em->flush();
	}
	
	/**
	 * method that return maximum storage of the warehouse
	 * @return int
	 */
	public function getWarehouseCapacity(): int
	{
		return $this->getStorageCapacityOrProduction("warehouse", "max_warehouse_storage");
	}
	
	/**
	 * method that return maximum storage of the warehouse
	 * @return int
	 */
	public function getGarnerCapacity(): int
	{
		return $this->getStorageCapacityOrProduction("garner", "max_garner_storage");
	}
	
	/**
	 * method that return production per hour of electricity station
	 * @return mixed
	 */
	public function getElectricityProduction()
	{
		return $this->getStorageCapacityOrProduction("electricity_station", "electricity_production", false);
	}
	
	/**
	 * method that return production per hour of fuel station
	 * @return mixed
	 */
	public function getFuelProduction()
	{
		return $this->getStorageCapacityOrProduction("fuel_station", "fuel_production", false);
	}
	
	/**
	 * method that return production per hour of iron station
	 * @return mixed
	 */
	public function getIronProduction()
	{
		return $this->getStorageCapacityOrProduction("iron_station", "iron_production", false);
	}
	
	/**
	 * method that return production per hour of water station
	 * @return mixed
	 */
	public function getWaterProduction()
	{
		return $this->getStorageCapacityOrProduction("water_station", "water_production", false);
	}
	
	/**
	 * methd that get the maximum capacity of a specific building (like warehouse of garner)
	 * @param string $building_array_name
	 * @param string $class_property (can be max_warehouse_storage or max_garner_storage)
	 * @param bool $is_storage
	 * @return mixed
	 */
	private function getStorageCapacityOrProduction(string $building_array_name, string $class_property, bool $is_storage = true)
	{
		if ($this->$class_property === null) {
			$level = 0;
			$building = $this->em->getRepository(\App\Entity\Building::class)->findOneBy([
				"base" => $this->getBase(),
				"array_name" => $building_array_name,
			]);
			
			if ($building) {
				$level = $building->getLevel();
			}
			
			$default_power = $this->globals->getBuildingsConfig()[$building_array_name]["default_power"];
			if ($is_storage === true) {
				$coef = $this->globals->getCoefForStorage()[$level];
			} else {
				$coef = $this->globals->getCoefForProduction()[$level];
			}
			
			if ($level === 0) {
				$this->$class_property = (int)$default_power;
			} else {
				$this->$class_property = (int)round($default_power * $level * (float)$coef);
			}
		}
		
		return $this->$class_property;
	}
	
	/**
	 * method that send resources that are needed to build a building
	 * @param string $array_name
	 * @return array
	 */
	public function getResourcesToBuild(string $array_name): array
	{
		$resource_tobuild = $this->globals->getBuildingsConfig()[$array_name]["resources_build"];
		$coef = $this->globals->getCoefForConstruction();
		$level = 0;
		
		$building = $this->em->getRepository(\App\Entity\Building::class)->findOneBy([
			"base" => $this->getBase(),
			"array_name" => $array_name,
		]);
		
		if ($building) {
			$level = $building->getLevel();
		}
		
		return [
			"electricity" => (int)round($resource_tobuild["electricity"] * ($level + 1) * ((double)$coef[$level+1])),
			"fuel" => (int)round($resource_tobuild["fuel"] * ($level + 1) * ((double)$coef[$level+1])),
			"iron" => (int)round($resource_tobuild["iron"] * ($level + 1) * ((double)$coef[$level+1])),
			"water" => (int)round($resource_tobuild["water"] * ($level + 1) * ((double)$coef[$level+1])),
		];
	}

	/**
	 * method that send number of each resource we can steal in a base
	 * @return array
	 */
	public function getResourcesToSteal(): array
	{
		$building = new Building($this->em, $this->globals, $this);
		$bunker = $this->em->getRepository(\App\Entity\Building::class)->findOneBy([
			"base" => $this->getBase(),
			"array_name" => "bunker",
		]);

		$protection = $bunker ? $building->getCurrentPower($bunker->getArrayName(), $bunker->getLevel()) : 0;

		return [
			"electricity" => $this->getBase()->getElectricity()*((100-$protection)/100) < 0 ? 0 : round($this->getBase()->getElectricity()*((100-$protection)/100)),
			"iron" => $this->getBase()->getIron()*((100-$protection)/100) < 0 ? 0 : round($this->getBase()->getIron()*((100-$protection)/100)),
			"fuel" => $this->getBase()->getFuel()*((100-$protection)/100) < 0 ? 0 : round($this->getBase()->getFuel()*((100-$protection)/100)),
			"water" => $this->getBase()->getWater()*((100-$protection)/100) < 0 ? 0 : round($this->getBase()->getWater()*((100-$protection)/100))
		];
	}
}