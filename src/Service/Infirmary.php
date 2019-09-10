<?php

namespace App\Service;

use Doctrine\ORM\EntityManagerInterface;

class Infirmary
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
	 * @var Building
	 */
	private $building;

	/**
	 * @var \App\Entity\Building
	 */
	private $infirmary;

	/**
	 * Building constructor.
	 * @param EntityManagerInterface $em
	 * @param Globals $globals
	 * @param Resources $resources
	 * @param Building $building
	 */
	public function __construct(EntityManagerInterface $em, Globals $globals, Resources $resources, Building $building)
	{
		$this->globals = $globals;
		$this->em = $em;
		$this->resources = $resources;
		$this->building = $building;
	}

	/**
	 * method to get infirmary of the base
	 * @return \App\Entity\Building
	 */
	private function getInfirmary(): \App\Entity\Building
	{
		if (!$this->infirmary) {
			$this->infirmary = $this->em->getRepository(\App\Entity\Building::class)->findOneBy([
				"array_name" => "infirmary",
				"base" => $this->globals->getCurrentBase()
			]);
		}

		return $this->infirmary;
	}

	/**
	 * method to return infirmary power
	 * @return int
	 */
	private function getCurrentInfirmaryPower(): int
	{
		return $this->building->getCurrentPower($this->getInfirmary()->getArrayName(), $this->getInfirmary()->getLevel());
	}

	/**
	 * method that send time to treat unit include reduction with infirmary power
	 * @param string $array_name
	 * @return int
	 */
	public function getTimeToTreat(string $array_name): int
	{
		$general_config = $this->globals->getGeneralConfig();
		$unit_config = $this->globals->getUnitsConfig("units")[$array_name];

		return round(($unit_config["recruitment_time"] / $general_config["cost_treat_unit_divider"]) * ((100-$this->getCurrentInfirmaryPower())/100));
	}

	/**
	 * method to get max unit that is possible to treat
	 * @param string $array_name
	 * @return mixed
	 */
	public function getMaxNumberOfUnitToTreat(string $array_name)
	{
		$general_config = $this->globals->getGeneralConfig();
		$base = $this->globals->getCurrentBase(true);
		$resources_totreat = $this->globals->getUnitsConfig("units")[$array_name]["resources_recruit"];
		$resources = [
			floor($base->getElectricity() / ($resources_totreat["electricity"] / $general_config["cost_treat_unit_divider"])),
			floor($base->getFuel() / ($resources_totreat["fuel"] / $general_config["cost_treat_unit_divider"])),
			floor($base->getIron() / ($resources_totreat["iron"] / $general_config["cost_treat_unit_divider"])),
			floor($base->getWater() / ($resources_totreat["water"] / $general_config["cost_treat_unit_divider"])),
			floor($base->getFood() / ($general_config["unit_food_consumption_hour"] * $general_config["cost_treat_unit_divider"]))
		];

		return min($resources);
	}

	/**
	 * test if base contain necessary resources to treat the number of units asked. If not return false else withdraw resources
	 * and return true
	 * @param string $array_name
	 * @param int $number_to_treat
	 * @return bool
	 */
	public function testWithdrawResourcesToTreat(string $array_name, int $number_to_treat): bool
	{
		$general_config = $this->globals->getGeneralConfig();
		$base = $this->globals->getCurrentBase(true);
		$resources = $this->resources;
		$resources_totreat = $this->globals->getUnitsConfig("units")[$array_name]["resources_recruit"];
		$electricity_remove = floor($resources_totreat["electricity"] / $general_config["cost_treat_unit_divider"]);
		$fuel_remove = floor($resources_totreat["fuel"] / $general_config["cost_treat_unit_divider"]);
		$iron_remove = floor($resources_totreat["iron"] / $general_config["cost_treat_unit_divider"]);
		$water_remove = floor($resources_totreat["water"] / $general_config["cost_treat_unit_divider"]);
		$food_remove = floor($general_config["unit_food_consumption_hour"] * $general_config["cost_treat_unit_divider"]);

		$rest_electricity = $base->getElectricity() - ($electricity_remove * $number_to_treat);
		$rest_fuel = $base->getFuel() - ($fuel_remove * $number_to_treat);
		$rest_iron = $base->getIron() - ($iron_remove * $number_to_treat);
		$rest_water = $base->getWater() - ($water_remove * $number_to_treat);
		$rest_food = $base->getFood() - ($food_remove * $number_to_treat);

		if ($rest_electricity < 0 || $rest_fuel < 0 || $rest_iron < 0 || $rest_water < 0 || $rest_food < 0) return false;

		$resources->withdrawResource("electricity", ($electricity_remove * $number_to_treat));
		$resources->withdrawResource("fuel", ($fuel_remove * $number_to_treat));
		$resources->withdrawResource("iron", ($iron_remove * $number_to_treat));
		$resources->withdrawResource("water", ($water_remove * $number_to_treat));
		$resources->withdrawResource("food", ($food_remove * $number_to_treat));

		return true;
	}
}