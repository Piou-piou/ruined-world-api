<?php

namespace App\Service;

use Doctrine\DBAL\DBALException;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\NonUniqueResultException;

class Unit
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
	 * Unit constructor.
	 * @param EntityManagerInterface $em
	 * @param Globals $globals
	 */
	public function __construct(EntityManagerInterface $em, Globals $globals)
	{
		$this->em = $em;
		$this->globals = $globals;
	}

	/**
	 * method to get attack or defense power of a unit
	 * @param \App\Entity\Unit $unit
	 * @param $type
	 * @return float
	 */
	public function getPower(\App\Entity\Unit $unit, $type): float
	{
		$default_power = $this->globals->getUnitsConfig()[$unit->getArrayName()][$type."_power"];

		if ($type === "attack") {
			$level = $unit->getAssaultLevel();
		} else {
			$level = $unit->getDefenseLevel();
		}

		return round($default_power * ((100+$level)/100));
	}

	/**
	 * method that return slower unit speed
	 * @param $units
	 * @return int
	 */
	public function getSlowerUnitSpeed($units): int
	{
		$slower_speed = null;

		foreach ($units as $array_name => $number) {
			$speed_unit = $this->globals->getUnitsConfig()[$array_name]["speed"];
			if ($slower_speed === null) {
				$slower_speed = $speed_unit;
			} else if ($speed_unit < $slower_speed) {
				$slower_speed = $speed_unit;
			}
		}

		return $slower_speed;
	}

	/**
	 * method that return max transport weight that unit can carry durin movement
	 * @param $units
	 * @return int
	 */
	public function getMaxCapacityTransport($units): int
	{
		$max_transport_weight = 0;

		/** @var \App\Entity\Unit $unit */
		foreach ($units as $unit) {
			$max_transport_weight += $this->globals->getUnitsConfig()[$unit->getArrayName()]["transport_weight"];
		}

		return $max_transport_weight;
	}

	/**
	 * method that test if we have enough unit of a type in our base
	 * @param array $units
	 * @return bool
	 * @throws NonUniqueResultException
	 */
	public function testEnoughUnitInBaseToSend(array $units)
	{
		if (count($units) === 0) {
			return false;
		}

		foreach ($units as $array_name => $number) {
			$unit_base = $this->em->getRepository(\App\Entity\Unit::class)->countSameUnitInBase($this->globals->getCurrentBase(),$array_name);
			if ($unit_base < $number->number || $number->number === 0) {
				return false;
			}
		}

		return true;
	}

	/**
	 * method to puts units on a specific movement
	 * @param array $units
	 * @param \App\Entity\UnitMovement $unit_movement
	 * @throws DBALException
	 */
	public function putUnitsInMovement(array $units, \App\Entity\UnitMovement $unit_movement)
	{
		foreach ($units as $array_name => $number) {
			$this->em->getRepository(\App\Entity\Unit::class)->putUnitsInMission($this->globals->getCurrentBase(), $unit_movement, $array_name, $number->number);
		}
	}
}