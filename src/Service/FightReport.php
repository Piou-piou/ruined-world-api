<?php

namespace App\Service;

class FightReport
{
	private $attack_units;
	private $defend_units;

	private $end_attack_units;
	private $end_defend_units;

	public function __construct()
	{
	}

	public function setStartAttackUnits($attack_units)
	{
		$start_attacked = clone $attack_units;
		$this->attack_units = $start_attacked;
	}

	public function setStartDefendUnits($defend_units)
	{
		$start_defend = clone $defend_units;
		$this->defend_units = $start_defend;
	}

	public function setEndAttackUnits($attack_units)
	{
		$this->end_attack_units = $attack_units;
	}

	public function setEndDefendUnits($defend_units)
	{
		$this->end_defend_units = $defend_units;
	}

	public function createReport(\App\Entity\UnitMovement $unitMovement)
	{
		$attack_units = [];
		foreach ($this->attack_units as $attack_unit) {
			if (array_key_exists($attack_unit->getArrayName(), $attack_units)) {
				$attack_units[$attack_unit->getArrayName()]["number"]++;
			} else {
				$attack_units[$attack_unit->getArrayName()] = [
					"number" => 1,
					"return_number" => 0
				];
			}
		}

		foreach ($this->end_attack_units as $attack_unit) {
			$attack_units[$attack_unit->getArrayName()]["return_number"]++;
		}

		dump($attack_units);
		dump('--------');
		dump($unitMovement);
	}
}