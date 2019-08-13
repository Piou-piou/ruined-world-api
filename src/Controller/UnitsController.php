<?php

namespace App\Controller;

use App\Entity\Unit;
use App\Service\Globals;
use App\Service\UnitMovement;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;

class UnitsController extends AbstractController
{
	/**
	 * method that send units currently in base
	 * @Route("/api/units/list-units-base/", name="units_list_base", methods={"POST"})
	 * @param SessionInterface $session
	 * @param Globals $globals
	 * @return JsonResponse
	 */
	public function sendUnitsInBase(SessionInterface $session, Globals $globals): JsonResponse
	{
		$units = $this->getDoctrine()->getRepository(Unit::class)->findByUnitsInBase($globals->getCurrentBase());

		return new JsonResponse([
			"success" => true,
			"token" => $session->get("user_token")->getToken(),
			"units" => $units
		]);
	}

	/**
	 * method that send units currently in movement
	 * @Route("/api/units/list-movements/", name="units_list_movements", methods={"POST"})
	 * @param SessionInterface $session
	 * @param UnitMovement $unitMovement
	 * @return JsonResponse
	 * @throws Exception
	 */
	public function sendUnitsInMovement(SessionInterface $session, UnitMovement $unitMovement): JsonResponse
	{
		return new JsonResponse([
			"success" => true,
			"token" => $session->get("user_token")->getToken(),
			"unit_movements" => $unitMovement->getCurrentMovementsInBase()
		]);
	}

	/**
	 * method that update all unit movements in the base
	 * @Route("/api/units/update-movements/", name="units_update_movements", methods={"POST"})
	 * @param SessionInterface $session
	 * @param Globals $globals
	 * @param UnitMovement $unit_movement
	 * @return JsonResponse
	 * @throws Exception
	 */
	public function updateUnitMovements(SessionInterface $session, Globals $globals, UnitMovement $unit_movement): JsonResponse
	{
		$unit_movement->updateUnitMovement($globals->getCurrentBase(true));

		return new JsonResponse([
			"success" => true,
			"token" => $session->get("user_token")->getToken()
		]);
	}
}