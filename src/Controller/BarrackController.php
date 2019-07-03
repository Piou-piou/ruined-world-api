<?php

namespace App\Controller;

use App\Service\Globals;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Routing\Annotation\Route;

class BarrackController extends AbstractController
{
	/**
	 * metho to send units that are possible to recruit
	 * @Route("/api/barrack/list-units-to-recruit/", name="list_units_to_recruit", methods={"POST"})
	 * @param Session $session
	 * @param Globals $globals
	 * @return JsonResponse
	 */
	public function sendUnitsPossibleToRecruit(Session $session, Globals $globals): JsonResponse
	{
		return new JsonResponse([
			"success" => true,
			"units" => $globals->getUnitsConfig(),
			"token" => $session->get("user")->getToken(),
		]);
	}
}