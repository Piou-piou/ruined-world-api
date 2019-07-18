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
		$units_config = $globals->getUnitsConfig();
		$attack_units = [];
		$base = $em->getRepository(Base::class)->find(1);
		$defend_units = $em->getRepository(Unit::class)->findBy([
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
			$attack_units[] = $unit;
		}

		dump($attack_units);
		dump($defend_units);

		$test = array_merge($defend_units, $attack_units);
		shuffle($test);

		foreach ($test as $unit) {
			if ($unit->getBase() === $base) {
				$attack_power = $units_config[$unit->getArrayName()]["attack_power"];
				$attack_key = count(array_keys($defend_units)) > 0 ? array_keys($defend_units)[0] : null;

				if ($attack_key !== null) {
					$defend_units[$attack_key]->setLife($defend_units[$attack_key]->getLife() - $attack_power);
					if ($defend_units[$attack_key]->getLife() <= 0) {
						unset($defend_units[$attack_key]);
					}
				}
			} else {
				$defend_power = $units_config[$unit->getArrayName()]["defense_power"];

				$defend_key = array_keys($attack_units)[0];
				$attack_units[$defend_key]->setLife($attack_units[$defend_key]->getLife() - $defend_power);
				if ($attack_units[$defend_key]->getLife() <= 0) {
					unset($attack_units[$defend_key]);
				}
			}
		}

		dump('----------------------');

		dump($attack_units);
		dump($defend_units);

		return new JsonResponse();
	}
}