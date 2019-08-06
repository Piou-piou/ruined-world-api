<?php

namespace App\Service;

use App\Entity\Unit;
use Doctrine\ORM\EntityManagerInterface;
use Exception;

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
	 * @var Building
	 */
	private $building;

	/**
	 * @var \App\Entity\Building
	 */
	private $barrack;

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
	 * mathod to get barrack of the base
	 * @return \App\Entity\Building
	 */
	private function getBarrack(): \App\Entity\Building
	{
		if (!$this->barrack) {
			$this->barrack = $this->em->getRepository(\App\Entity\Building::class)->findOneBy([
				"array_name" => "barrack",
				"base" => $this->globals->getCurrentBase()
			]);
		}

		return $this->barrack;
	}

	/**
	 * method to return barrack power
	 * @return int
	 */
	private function getCurrentBarrackPower(): int
	{
		return $this->building->getCurrentPower($this->getBarrack()->getArrayName(), $this->getBarrack()->getLevel());
	}

	/**
	 * method that send time to recruit units include reduction with barrack power
	 * @param int $time
	 * @return int
	 */
	public function getTimeToRecruit(int $time): int
	{
		return round($time * ((100-$this->getCurrentBarrackPower())/100));
	}

	/**
	 * method to get max unit that is possible to recruit
	 * @param string $array_name
	 * @return mixed
	 */
	private function getMaxNUmberOfUnitToRecruit(string $array_name)
	{
		$base = $this->globals->getCurrentBase(true);
		$resources_torecruit = $this->globals->getUnitsConfig("units")[$array_name]["resources_recruit"];
		$resources = [
			floor($base->getElectricity() / $resources_torecruit["electricity"]),
			floor($base->getFuel() / $resources_torecruit["fuel"]),
			floor($base->getIron() / $resources_torecruit["iron"]),
			floor($base->getWater() / $resources_torecruit["water"])
		];

		return min($resources);
	}

	/**
	 * method that send units possible to recruit with time reduced (depend on barrack lvl for power)
	 * @return mixed
	 */
	public function getUnitsPossibleToRecruit()
	{
		$units = $this->globals->getUnitsConfig("units");

		foreach ($units as $unit) {
			$units[$unit["array_name"]]["recruitment_time"] = $this->getTimeToRecruit($unit["recruitment_time"]);
			$units[$unit["array_name"]]["max_recruit_possible"] = $this->getMaxNUmberOfUnitToRecruit($unit["array_name"]);
		}

		return $units;
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
		$resources_torecruit = $this->globals->getUnitsConfig("units")[$array_name]["resources_recruit"];;

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

	/**
	 * method that end all recruitment that are terminated
	 * @throws Exception
	 */
	public function endRecruitmentUnitsInBase()
	{
		$units = $this->em->getRepository(Unit::class)->findByRecruitmentEnded($this->globals->getCurrentBase());

		/**
		 * @var $unit Unit
		 */
		foreach ($units as $unit) {
			$unit->setInRecruitment(false);
			$unit->setEndRecruitment(null);
			$this->em->persist($unit);
		}

		$this->em->flush();
	}
}