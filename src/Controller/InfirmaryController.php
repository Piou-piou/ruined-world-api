<?php

namespace App\Controller;

use App\Entity\Unit;
use App\Service\Globals;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;

class InfirmaryController extends AbstractController
{
	/**
	 * method that send units currently in base with life inferior to 100 to treat them
	 * @Route("/api/units/list-units-to-treat/", name="units_list_to_treat", methods={"POST"})
	 * @param SessionInterface $session
	 * @param Globals $globals
	 * @return JsonResponse
	 */
	public function sendUnitsToTreat(SessionInterface $session, Globals $globals): JsonResponse
	{
		$units = $this->getDoctrine()->getRepository(Unit::class)->findByUnitsInBaseToTreat($globals->getCurrentBase());

		return new JsonResponse([
			"success" => true,
			"token" => $session->get("user_token")->getToken(),
			"units" => $units,
			"unit_config" => $globals->getUnitsConfig("units")
		]);
	}
}