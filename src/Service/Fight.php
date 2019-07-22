<?php

namespace App\Service;

use App\Entity\Base;
use App\Entity\Unit;

class Fight
{
	/**
	 * @var Globals
	 */
	private $globals;

	/**
	 * Fight constructor.
	 * @param Globals $globals
	 */
	public function __construct(Globals $globals)
	{
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

	public function attackBase(Base $base, \App\Entity\UnitMovement $unit_movement, Base $attacked_base)
	{

	}
}