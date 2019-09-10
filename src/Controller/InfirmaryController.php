<?php

namespace App\Controller;

use App\Entity\Unit;
use App\Service\Api;
use App\Service\Globals;
use App\Service\Infirmary;
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
	 * @param Infirmary $infirmary
	 * @return JsonResponse
	 * @throws AnnotationException
	 * @throws ExceptionInterface
	 */
	public function sendUnitsToTreat(SessionInterface $session, Globals $globals, Api $api, Infirmary $infirmary): JsonResponse
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
			$number = 1;

			if ($unit->getLife() < $max_life) {
				if (isset($return_units[$unit->getArrayName()])) {
					$number++;
					$return_units[$unit->getArrayName()]["number_to_treat"] = $number;
					$return_units[$unit->getArrayName()]["possible_to_treat"] = $infirmary->getMaxNumberOfUnitToTreat($unit->getArrayName()) > $number ? $number :  $infirmary->getMaxNumberOfUnitToTreat($unit->getArrayName());
				} else {
					$return_units[$unit->getArrayName()] = [
						"unit" => $unit,
						"number_to_treat" => $number,
						"resources_to_treat" => [
							"electricity" => floor($config["resources_recruit"]["electricity"]/$general_config["cost_treat_unit_divider"]),
							"fuel" => floor($config["resources_recruit"]["fuel"]/$general_config["cost_treat_unit_divider"]),
							"iron" => floor($config["resources_recruit"]["iron"]/$general_config["cost_treat_unit_divider"]),
							"water" => floor($config["resources_recruit"]["water"]/$general_config["cost_treat_unit_divider"]),
							"food" => floor($general_config["unit_food_consumption_hour"]*$general_config["cost_treat_unit_divider"]),
						],
						"treatment_time" => $infirmary->getTimeToTreat($unit->getArrayName()),
						"possible_to_treat" => $infirmary->getMaxNumberOfUnitToTreat($unit->getArrayName()) > $number ? $number :  $infirmary->getMaxNumberOfUnitToTreat($unit->getArrayName())
					];
				}
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