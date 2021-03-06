<?php

namespace App\Service;

use App\Entity\Base;
use App\Entity\Unit;
use DateInterval;
use DateTime;
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
	 * @var Resources
	 */
	private $resources;

	/**
	 * @var \App\Service\Unit
	 */
	private $unit_service;

	/**
	 * @var FightReport
	 */
	private $fight_report;

	/** @var Building */
	private $building;

	private $defenses_power;

	/**
	 * Fight constructor.
	 * @param EntityManagerInterface $em
	 * @param Globals $globals
	 * @param Resources $resources
	 * @param \App\Service\Unit $unit_service
	 * @param FightReport $fightReport
	 */
	public function __construct(EntityManagerInterface $em, Globals $globals, Resources $resources, \App\Service\Unit $unit_service, FightReport $fightReport, Building $building)
	{
		$this->em = $em;
		$this->globals = $globals;
		$this->resources = $resources;
		$this->unit_service = $unit_service;
		$this->fight_report = $fightReport;
		$this->building = $building;
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
		$power = $this->unit_service->getPower($unit, $type);
		$key = count(array_keys($units)) > 0 ? array_keys($units)[0] : null;
		$use_defenses = $this->defenses_power === 0 ?  0 : rand(0, 1);

		if ($use_defenses === 1 && $type === "attack") {
			if ($unit->getArmor() > 0) {
				$unit->setArmor($unit->getArmor() - $this->defenses_power);
				if ($unit->getArmor() < 0) {
					$life_to_delete = abs($unit->getArmor());
					$unit->setArmor(0);
					$unit->setLife($unit->getLife() - $life_to_delete);
				}
			} else {
				$unit->setLife($unit->getLife() - $this->defenses_power);
			}
			$this->fight_report->setDefenseDamage($this->fight_report->getDefenseDamage() + $this->defenses_power);

			if ($unit->getLife() <= 0) {
				$this->fight_report->setDefenseKill($this->fight_report->getDefenseKill() + 1);
				return $units;
			}
		}

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
	 * method to kill units with life equal to 0 after fight
	 * @param array $units
	 */
	public function killUnitAfterFight(array $units)
	{
		foreach ($units as $unit) {
			if ($unit->getLife() <= 0) {
				$this->em->remove($unit);
			} else {
				$this->em->persist($unit);
			}
		}
		$this->em->flush();
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
		$defenses = $this->em->getRepository(\App\Entity\Building::class)->findOneBy([
			"base" => $attacked_base,
			"array_name" => "defenses"
		]);
		if (!$defenses || $defenses->getLevel() === 0) {
			$this->defenses_power = 0;
		} else {
			$this->defenses_power = $this->building->getCurrentPower($defenses->getArrayName(), $defenses->getLevel());
		}

		$this->fight_report->setStartAttackUnits($base_attack_units);
		$this->fight_report->setStartDefendUnits($defend_units);

		$all_units = array_merge($attack_units, $defend_units);
		shuffle($all_units);

		foreach ($all_units as $unit) {
			if ($unit->getBase()->getId() === $base->getId()) {
				$defend_units = $this->attackOrDefendUnit($unit, $defend_units, "attack");
			} else {
				$attack_units = $this->attackOrDefendUnit($unit, $attack_units, "defense");
			}

			if ($unit->getLife() <= 0) {
				$this->em->remove($unit);
			} else {
				$this->em->persist($unit);
			}
		}
		$this->em->flush();

		$this->killUnitAfterFight($defend_units);
		$this->killUnitAfterFight($base_attack_units->toArray());

		$this->putUnitsOnReturn($base_attack_units, $unit_movement);
		if ($base_attack_units->count() > 0) {
			$this->stealResources($base_attack_units, $unit_movement, $attacked_base);
		}

		$this->fight_report->setEndAttackUnits($base_attack_units);
		$this->fight_report->setEndDefendUnits($defend_units);
		$this->fight_report->createReport($unit_movement);
	}

	/**
	 * method that add stolen resources to the base and end movement
	 * @param \App\Entity\UnitMovement $unit_movement
	 */
	public function endMovement(\App\Entity\UnitMovement $unit_movement)
	{
		$this->resources->setBase($this->globals->getCurrentBase());
		$this->resources->addResource("electricity", $unit_movement->getElectricity());
		$this->resources->addResource("iron", $unit_movement->getIron());
		$this->resources->addResource("fuel", $unit_movement->getFuel());
		$this->resources->addResource("water", $unit_movement->getWater());

		$unit_movement->clearUnits();
		$this->em->persist($unit_movement);
		$this->em->flush();
		$this->em->remove($unit_movement);
		$this->em->flush();
	}

	/**
	 * method to put units on return if there is units on movement else delete it
	 * @param $base_attack_units
	 * @param \App\Entity\UnitMovement $unit_movement
	 * @throws Exception
	 */
	private function putUnitsOnReturn($base_attack_units, \App\Entity\UnitMovement $unit_movement) {
		if ($base_attack_units->count() === 0) {
			$this->em->remove($unit_movement);
		} else {
			$now = new DateTime();
			$unit_movement->setMovementType(\App\Entity\UnitMovement::MOVEMENT_TYPE_RETURN);
			$unit_movement->setEndDate($now->add(new DateInterval("PT".$unit_movement->getDuration()."S")));
			$this->em->persist($unit_movement);
		}
		$this->em->flush();
	}

	/**
	 * method to steal resources in base after attack it
	 * @param $base_attack_units
	 * @param \App\Entity\UnitMovement $unitMovement
	 * @param Base $attacked_base
	 */
	private function stealResources($base_attack_units, \App\Entity\UnitMovement $unitMovement, Base $attacked_base)
	{
		$unit_config = $this->globals->getUnitsConfig();
		$this->resources->setBase($attacked_base);
		$resources_steal = $this->resources->getResourcesToSteal();
		$resource_names = ["electricity", "iron", "fuel", "water"];

		/** @var Unit $unit */
		foreach ($base_attack_units as $unit) {
			$transport_weight = $unit_config[$unit->getArrayname()]["transport_weight"];
			$resource_name = $resource_names[rand(0, 3)];
			$resource = $resources_steal[$resource_name];

			if ($resource - $transport_weight > 0) {
				$setter = "set".ucfirst($resource_name);
				$getter = "get".ucfirst($resource_name);
				$unitMovement->$setter($unitMovement->$getter() + $transport_weight);
				$this->resources->withdrawResource($resource_name, $transport_weight);
			}
		}

		$this->em->persist($unitMovement);
		$this->em->flush();
	}
}