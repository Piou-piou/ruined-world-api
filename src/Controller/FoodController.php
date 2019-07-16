<?php

namespace App\Controller;

use App\Entity\Unit;
use App\Service\Globals;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;

class FoodController extends AbstractController
{
	/**
	 * @Route("/api/food/consumption-per-hour/", name="food_consumption_hour", methods={"POST"})
	 * @param SessionInterface $session
	 * @param Globals $globals
	 * @return JsonResponse
	 */
	public function foodConsumptionPerHour(SessionInterface $session, Globals $globals): JsonResponse
	{
		$em = $this->getDoctrine()->getManager();
		$base = $globals->getCurrentBase(true);
		$units = $em->getRepository(Unit::class)->findByUnitsInBase($base);
		$string = "consommé par heure";

		if ($base->getFood() === 0 && count($units) > 0) {
			$string = "mort par heure";
		}

		return new JsonResponse([
			"success" => true,
			"token" => $session->get("user")->getToken(),
			"food_consumption" => count($units) * 2,
			"food_string" => $string
		]);
	}
}