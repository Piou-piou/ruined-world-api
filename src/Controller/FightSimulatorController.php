<?php

namespace App\Controller;

use App\Entity\Base;
use App\Entity\Unit;
use App\Service\Fight;
use App\Service\Globals;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;

class FightSimulatorController extends AbstractController
{

	/**
	 * @Route("/api/fight/all-units-type/", name="figth_all_units_types", methods={"POST"})
	 * @param SessionInterface $session
	 * @param Globals $globals
	 * @return JsonResponse
	 */
	public function sendAllUnitTypes(SessionInterface $session, Globals $globals): JsonResponse
	{
		$units_config = $globals->getUnitsConfig();

		return new JsonResponse([
			"success" => true,
			"token" => $session->get("user_token")->getToken(),
			"units" => $units_config
		]);
	}

	/**
	 * @Route("/api/fight/simulate/", name="fight_simulate")
	 * @param SessionInterface $session
	 * @param Globals $globals
	 * @param Fight $fight
	 * @return JsonResponse
	 */
	public function simulateFight(SessionInterface $session, Globals $globals, Fight $fight): JsonResponse
	{
		$infos = $session->get("jwt_infos");
		$base = new Base();
		$base->setId(1);
		$other_base_units = $this->createUnits($globals,"defense", $infos->defense_units);
		$base_units = $this->createUnits($globals,"attack", $infos->attack_units);

		$test = array_merge($other_base_units, $base_units);
		shuffle($test);

		foreach ($test as $unit) {
			if ($unit->getBase()->getId() === $base->getId()) {
				$other_base_units = $fight->attackOrDefendUnit($unit, $other_base_units, "attack");
			} else {
				$base_units = $fight->attackOrDefendUnit($unit, $base_units, "defense");
			}
		}

		return new JsonResponse([
			"success" => true,
			"token" => $session->get("user_token")->getToken(),
			"attack_units" => $this->createUnitsArrayForApp($globals, $base_units),
			"defense_units" => $this->createUnitsArrayForApp($globals, $other_base_units)
		]);
	}

	/**
	 * method that create units of attack and defense
	 * @param Globals $globals
	 * @param string $type
	 * @param $units
	 * @return array
	 */
	private function createUnits(Globals $globals, string $type, $units): array
	{
		$units_config = $globals->getUnitsConfig();
		$return_units = [];
		$base = new Base();
		$base->setId(1);
		if ($type === "defense") $base->setId(2);

		foreach ($units as $array_name => $number) {
			for ($i=0 ; $i<$number ; $i++) {
				$unit = new Unit();
				$unit->setName($array_name);
				$unit->setArrayName($array_name);
				$unit->setAssaultLevel(1);
				$unit->setDefenseLevel(1);
				$unit->setLife($units_config[$array_name]["life"]);
				$unit->setArmor($units_config[$array_name]["armor"]);
				$unit->setBase($base);
				$return_units[] = $unit;
			}
		}

		return $return_units;
	}

	/**
	 * method that format units for front
	 * @param Globals $globals
	 * @param array $units
	 * @return array
	 */
	private function createUnitsArrayForApp(Globals $globals, array $units): array
	{
		$units_config = $globals->getUnitsConfig();
		$return_units = [];

		foreach ($units as $unit) {
			if (array_key_exists($unit->getArrayName(), $return_units)) {
				$return_units[$unit->getArrayName()]["number"]++;
			} else {
				$return_units[$unit->getArrayName()] = [
					"name" => $units_config[$unit->getArrayName()]["name"],
					"number" => 1
				];
			}
		}

		return $return_units;
	}
}