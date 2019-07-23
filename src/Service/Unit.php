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