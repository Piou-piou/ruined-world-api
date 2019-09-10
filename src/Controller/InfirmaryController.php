<?php

namespace App\Controller;

use App\Entity\Unit;
use App\Service\Api;
use App\Service\Globals;
use Doctrine\Common\Annotations\AnnotationException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Exception\ExceptionInterface;

class InfirmaryController extends AbstractController
{
	/**
	 * method that send units currently in base with life inferior to 100 to treat them
	 * @Route("/api/units/list-units-to-treat/", name="units_list_to_treat", methods={"POST"})
	 * @param SessionInterface $session
	 * @param Globals $globals
	 * @param Api $api
	 * @return JsonResponse
	 * @throws AnnotationException
	 * @throws ExceptionInterface
	 */
	public function sendUnitsToTreat(SessionInterface $session, Globals $globals, Api $api): JsonResponse
	{
		$unit_config = $globals->getUnitsConfig("units");
		$general_config = $globals->getGeneralConfig();
		$units = $this->getDoctrine()->getRepository(Unit::class)->findBy([
			"base" => $globals->getCurrentBase(),
			"in_recruitment" => false,
			"unitMovement" => null
		]);
		$return_units = [];

		foreach ($units as $unit) {
			$config = $unit_config[$unit->getArrayName()];
			$max_life = $config["life"];

			if ($unit->getLife() < $max_life) {
				$return_units[] = [
					"unit" => $unit,
					"resources_to_treat" => [
						"electricity" => round($config["resources_recruit"]["electricity"]/$general_config["cost_treat_unit_divider"]),
						"fuel" => round($config["resources_recruit"]["fuel"]/$general_config["cost_treat_unit_divider"]),
						"iron" => round($config["resources_recruit"]["iron"]/$general_config["cost_treat_unit_divider"]),
						"water" => round($config["resources_recruit"]["water"]/$general_config["cost_treat_unit_divider"]),
						"food" => round($general_config["unit_food_consumption_hour"]*$general_config["cost_treat_unit_divider"]),
					],
					"treatment_time" => round($config["recruitment_time"]/$general_config["cost_treat_unit_divider"])
				];
			}
		}

		return new JsonResponse([
			"success" => true,
			"token" => $session->get("user_token")->getToken(),
			"units" => $api->serializeObject($return_units),
			"unit_config" => $globals->getUnitsConfig("units")
		]);
	}
}