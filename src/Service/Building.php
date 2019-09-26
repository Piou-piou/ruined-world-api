<?php

namespace App\Service;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

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
	 * @var SessionInterface
	 */
	private $session;

	/**
	 * Building constructor.
	 * @param SessionInterface $session
	 * @param EntityManagerInterface $em
	 * @param Globals $globals
	 * @param Resources $resources
	 */
	public function __construct(SessionInterface $session, EntityManagerInterface $em, Globals $globals, Resources $resources)
	{
		$this->globals = $globals;
		$this->em = $em;
		$this->resources = $resources;
		$this->session = $session;
	}
	
	/**
	 * this method return the construction time of a building
	 * @param string $array_name
	 * @param int $level
	 * @return int
	 */
	public function getConstructionTime(string $array_name, int $level): int
	{
		$level = $level + 1;
		$building_config = $this->globals->getBuildingsConfig()[$array_name];
		
		if ($level === 0) {
			return (int)$building_config["construction_time"];
		} else {
			return (int)round($building_config["construction_time"] * $level);
		}
	}

	/**
	 * method to return current and next power string with correct value in to_replace
	 * @param string $array_name
	 * @param int $level
	 * @return array
	 */
	public function getExplanationStringPower(string $array_name, int $level): array
	{
		$building_config = $this->globals->getBuildingsConfig()[$array_name];
		$explanation_current = str_replace("[[to_replace]]", $this->getCurrentPower($array_name, $level), $building_config["explanation_current_power"]);
		$explanation_next = str_replace("[[to_replace]]", $this->getCurrentPower($array_name, $level+1), $building_config["explanation_next_power"]);

		return [
			"current" => $explanation_current,
			"next" => $explanation_next
		];
	}

	/**
	 * method to return current power of a building calculated by type
	 * @param string $array_name
	 * @param int $level
	 * @return int
	 */
	public function getCurrentPower(string $array_name, int $level): int
	{
		$building_config = $this->globals->getBuildingsConfig()[$array_name];
		$default_power = $building_config["default_power"];

		if ($level === 0) {
			return 0;
		}

		if ($building_config["power_type"] === "reduction") {
			return $default_power * $level;
		} else if ($building_config["power_type"] === "production") {
			$coef = $this->globals->getCoefForProduction()[$level];
			return (int)round($default_power * $level * (float)$coef);
		} else if ($building_config["power_type"] === "storage") {
			$coef = $this->globals->getCoefForStorage()[$level];
			return (int)round($default_power * $level * (float)$coef);
		} else if ($building_config["power_type"] === "defense") {
			return $default_power + $level;
		} else {
			return $default_power * $level;
		}
	}

	/**
	 * method to get when is possible to build a building if premium enabled
	 * @param string $array_name
	 * @return float|int
	 */
	public function getWhenIsPossibleToUpgrade(string $array_name)
	{
		if (!$this->session->get("user")->hasPremiumUpgradeBuilding()) {
			return 0;
		}

		$base = $this->globals->getCurrentBase(true);
		$resources_tobuild = $this->resources->getResourcesToBuild($array_name);
		$resource_name_tocalc_time = "";
		$resource_tocalc_time = 0;

		foreach ($resources_tobuild as $key => $resource) {
			$getter = "get".ucfirst($key);
			$resource_del = $base->$getter() - $resource;

			if ($resource_del < 0 && $resource_del < $resource_tocalc_time) {
				$resource_name_tocalc_time = $key;
				$resource_tocalc_time = $resource_del;
			}
		}

		$getter_production = "get".ucfirst($resource_name_tocalc_time)."Production";

		return abs($resource_tocalc_time) > 0 ? round(abs($resource_tocalc_time) / $this->resources->$getter_production(), 1) : 0;
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