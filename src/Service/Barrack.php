<?php

namespace App\Service;

use Doctrine\ORM\EntityManagerInterface;

class Barrack
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
	 * test if base contain necessary resources to recruit the number of units asked. If not return false else withdraw resources
	 * and return true
	 * @param string $array_name
	 * @param int $number_to_recruit
	 * @return bool
	 */
	public function testWithdrawResourcesToRecruit(string $array_name, int $number_to_recruit): bool
	{
		$base = $this->globals->getCurrentBase(true);
		$resources = $this->resources;
		$resources_torecruit = $this->globals->getUnitsConfig()[$array_name]["resources_recruit"];;

		$rest_electricity = $base->getElectricity() - ($resources_torecruit["electricity"] * $number_to_recruit);
		$rest_fuel = $base->getFuel() - ($resources_torecruit["fuel"] * $number_to_recruit);
		$rest_iron = $base->getIron() - ($resources_torecruit["iron"] * $number_to_recruit);
		$rest_water = $base->getWater() - ($resources_torecruit["water"] * $number_to_recruit);

		if ($rest_electricity < 0 || $rest_fuel < 0 || $rest_iron < 0 || $rest_water < 0) return false;

		$resources->withdrawResource("electricity", ($resources_torecruit["electricity"] * $number_to_recruit));
		$resources->withdrawResource("fuel", ($resources_torecruit["fuel"] * $number_to_recruit));
		$resources->withdrawResource("iron", ($resources_torecruit["iron"] * $number_to_recruit));
		$resources->withdrawResource("water", ($resources_torecruit["water"] * $number_to_recruit));

		return true;
	}
}