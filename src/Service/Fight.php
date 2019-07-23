<?php

namespace App\Service;

use App\Entity\Base;
use App\Entity\Unit;
use DateInterval;
use Doctrine\ORM\EntityManagerInterface;
use Exception;

class Fight
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
	 * Fight constructor.
	 * @param EntityManagerInterface $em
	 * @param Globals $globals
	 */
	public function __construct(EntityManagerInterface $em, Globals $globals)
	{
		$this->em = $em;
		$this->globals = $globals;
	}

	/**
	 * method that put damage on unit when is in defense or get damage from a defense unit
	 * @param Unit $unit
	 * @param array $units
	 * @param string $type
	 * @return array
	 */
	public function attackOrDefendUnit(Unit $unit, array $units, string $type = "attack"): array
	{
		$units_config = $this->globals->getUnitsConfig();
		$power = $units_config[$unit->getArrayName()][$type."_power"];
		$key = count(array_keys($units)) > 0 ? array_keys($units)[0] : null;

		if ($key !== null) {
			$unit_key = $units[$key];
			if ($unit_key->getArmor() > 0) {
				$life_to_delete = 0;
				$unit_key->setArmor($unit_key->getArmor() - $power);
			} else {
				$life_to_delete = $power;
			}

			if ($unit_key->getArmor() < 0) {
				$life_to_delete = abs($unit_key->getArmor());
				$unit_key->setArmor(0);
			}

			$unit_key->setLife($unit_key->getLife() - $life_to_delete);

			if ($unit_key->getLife() <= 0) {
				$delete_for_next = abs($unit_key->getLife());
				unset($units[$key]);
				$key = count(array_keys($units)) > 0 ? array_keys($units)[0] : null;

				if ($key !== null) {
					$units[$key]->setLife($units[$key]->getLife() - $delete_for_next);
				}
			}
		}

		return $units;
	}

	/**
	 * method that handle attack a base with units kill necessary units and if there is units in movement
	 * after attack, put them on return
	 * @param Base $base
	 * @param \App\Entity\UnitMovement $unit_movement
	 * @param Base $attacked_base
	 * @throws Exception
	 */
	public function attackBase(Base $base, \App\Entity\UnitMovement $unit_movement, Base $attacked_base)
	{
		$base_attack_units = $unit_movement->getUnits();
		$attack_units = $base_attack_units->toArray();
		$defend_units = $this->em->getRepository(Unit::class)->findBy([
			"base" => $attacked_base,
			"in_recruitment" => false,
			"unitMovement" => null
		]);

		$all_units = array_merge($attack_units, $defend_units);
		shuffle($all_units);

		foreach ($all_units as $unit) {
			if ($unit->getBase()->getId() === $base->getId()) {
				$this->attackOrDefendUnit($unit, $defend_units, "attack");
			} else {
				$this->attackOrDefendUnit($unit, $attack_units, "defense");
			}

			if ($unit->getLife() <= 0) {
				$this->em->remove($unit);
			} else {
				$this->em->persist($unit);
			}
		}
		$this->em->flush();

		if ($base_attack_units->count() === 0) {
			$this->em->remove($unit_movement);
		} else {
			$now = new \DateTime();
			$unit_movement->setMovementType(\App\Entity\UnitMovement::MOVEMENT_TYPE_RETURN);
			$unit_movement->setEndDate($now->add(new DateInterval("PT".$unit_movement->getDuration()."S")));
			$this->em->persist($unit_movement);
			$this->em->flush();
		}
	}
}