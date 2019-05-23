<?php

namespace App\Service;

use Doctrine\ORM\EntityManagerInterface;

class Building
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
	 * @var Resources
	 */
	private $resources;
	
	/**
	 * Building constructor.
	 * @param EntityManagerInterface $em
	 * @param Globals $globals
	 * @param Resources $resources
	 */
	public function __construct(EntityManagerInterface $em, Globals $globals, Resources $resources)
	{
		$this->globals = $globals;
		$this->em = $em;
		$this->resources = $resources;
	}
	
	/**
	 * this method return the construction time of a building
	 * @param string $array_name
	 * @param int $level
	 * @return int
	 */
	public function getConstructionTime(string $array_name, int $level): int
	{
		$building_config = $this->globals->getBuildingsConfig()[$array_name];
		
		if ($level === 0) {
			return (int)$building_config["construction_time"];
		} else {
			return (int)round($building_config["construction_time"] * $level);
		}
	}
	
	/**
	 * method that end all construction that are terminated
	 */
	public function endConstructionBuildingsInBase()
	{
		$buildings = $this->em->getRepository(\App\Entity\Building::class)->finByBuildingInConstructionEnded($this->globals->getCurrentBase());
		
		/**
		 * @var $building \App\Entity\Building
		 */
		foreach ($buildings as $building) {
			$building->setLevel($building->getLevel() + 1);
			$building->setInConstruction(false);
			$building->setEndConstruction(null);
			$this->em->persist($building);
		}
		
		$this->em->flush();
	}
	
	/**
	 * test if base contain necessary resources to build the building if not return false else withdraw resources
	 * and return true
	 * @param string $array_name
	 * @return bool
	 */
	public function testWithdrawResourcesToBuild(string $array_name)
	{
		$base = $this->globals->getCurrentBase(true);
		$resources = $this->resources;
		$resources_tobuild = $resources->getResourcesToBuild($array_name);
		
		$rest_electricity = $base->getElectricity() - $resources_tobuild["electricity"];
		$rest_fuel = $base->getFuel() - $resources_tobuild["fuel"];
		$rest_iron = $base->getIron() - $resources_tobuild["iron"];
		$rest_water = $base->getWater() - $resources_tobuild["water"];
		
		if ($rest_electricity < 0 || $rest_fuel < 0 || $rest_iron < 0 || $rest_water < 0) return false;
		
		$resources->withdrawResource("electricity", $resources_tobuild["electricity"]);
		$resources->withdrawResource("fuel", $resources_tobuild["fuel"]);
		$resources->withdrawResource("iron", $resources_tobuild["iron"]);
		$resources->withdrawResource("water", $resources_tobuild["water"]);
		
		return true;
	}
}