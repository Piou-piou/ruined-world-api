<?php

namespace App\Controller;

use App\Entity\Unit;
use App\Service\Globals;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;

class UnitsController extends AbstractController
{
	/**
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
			"token" => $session->get("user")->getToken(),
			"units" => $units
		]);
	}
}