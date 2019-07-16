<?php

namespace App\Controller;

use App\Service\Food;
use App\Service\Globals;
use Doctrine\ORM\NonUniqueResultException;
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
	 * @param Food $food
	 * @return JsonResponse
	 * @throws NonUniqueResultException
	 */
	public function foodConsumptionPerHour(SessionInterface $session, Globals $globals, Food $food): JsonResponse
	{
		$base = $globals->getCurrentBase(true);
		$food_consumption = $food->getFoodConsumedPerHour();
		$string = "consommé par heure";

		if ($base->getFood() === 0 && $food_consumption > 0) {
			$string = "mort par heure";
		}

		return new JsonResponse([
			"success" => true,
			"token" => $session->get("user")->getToken(),
			"food_consumption" => $food_consumption,
			"food_string" => $string
		]);
	}
}