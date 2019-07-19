<?php

namespace App\Controller;

use App\Entity\Base;
use App\Entity\Unit;
use App\Service\Globals;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;

class FightSimulatorController extends AbstractController
{

	/**
	 * @Route("/api/fight/simulate/", name="fight_simulate")
	 * @param SessionInterface $session
	 * @param Globals $globals
	 * @return JsonResponse
	 */
	public function simulateFight(SessionInterface $session, Globals $globals): JsonResponse
	{
		$em = $this->getDoctrine()->getManager();
		$base_units = [];
		$base = $em->getRepository(Base::class)->find(1);
		$other_base_units = $em->getRepository(Unit::class)->findBy([
			"base" => $em->getRepository(Base::class)->find(8),
			"in_recruitment" => false,
			"unitMovement" => null
		]);
		for ($i=0 ; $i<9 ; $i++) {
			$unit = new Unit();
			$unit->setName("Villager");
			$unit->setArrayName("villager");
			$unit->setAssaultLevel(1);
			$unit->setDefenseLevel(1);
			$unit->setLife(100);
			$unit->setBase($base);
			$base_units[] = $unit;
		}

		dump($base_units);
		dump($other_base_units);

		$test = array_merge($other_base_units, $base_units);
		shuffle($test);

		foreach ($test as $unit) {
			if ($unit->getBase() === $base) {
				$other_base_units = $this->attackOrDefendUnit($globals, $unit, $other_base_units, "attack");
			} else {
				$base_units = $this->attackOrDefendUnit($globals, $unit, $base_units, "defense");
			}
		}

		dump('----------------------');

		dump($base_units);
		dump($other_base_units);

		return new JsonResponse();
	}

	private function attackOrDefendUnit(Globals $globals, Unit $unit, array $units, string $type = "attack") {
		$units_config = $globals->getUnitsConfig();
		$power = $units_config[$unit->getArrayName()][$type."_power"];
		$key = count(array_keys($units)) > 0 ? array_keys($units)[0] : null;

		if ($key !== null) {
			$units[$key]->setLife($units[$key]->getLife() - $power);

			if ($units[$key]->getLife() <= 0) {
				$delete_for_next = abs($units[$key]->getLife());
				unset($units[$key]);
				$key = count(array_keys($units)) > 0 ? array_keys($units)[0] : null;

				if ($key !== null) {
					$units[$key]->setLife($units[$key]->getLife() - $delete_for_next);
				}
			}
		}

		return $units;
	}
}