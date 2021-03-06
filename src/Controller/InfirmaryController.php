<?php

namespace App\Controller;

use App\Entity\Unit;
use App\Service\Api;
use App\Service\Globals;
use App\Service\Infirmary;
use App\Service\Point;
use DateInterval;
use Doctrine\Common\Annotations\AnnotationException;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Exception\ExceptionInterface;

class InfirmaryController extends AbstractController
{
	/**
	 * method that send units currently in base with life inferior to 100 to treat them
	 * @Route("/api/infirmary/list-units-to-treat/", name="infirmary_list_to_treat", methods={"POST"})
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
			"in_treatment" => false,
			"in_recruitment" => false,
			"unitMovement" => null
		]);
		$return_units = [];

		foreach ($units as $unit) {
			$config = $unit_config[$unit->getArrayName()];
			$max_life = $config["life"];

			if ($unit->getLife() < $max_life) {
				if (isset($return_units[$unit->getArrayName()])) {
					$return_units[$unit->getArrayName()]["number_to_treat"] = $return_units[$unit->getArrayName()]["number_to_treat"] + 1;
					$number = $return_units[$unit->getArrayName()]["number_to_treat"];
					$return_units[$unit->getArrayName()]["possible_to_treat"] = $infirmary->getMaxNumberOfUnitToTreat($unit->getArrayName()) > $number ? $number :  $infirmary->getMaxNumberOfUnitToTreat($unit->getArrayName());
				} else {
					$number = 1;
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

	/**
	 * method to set time of end treatment of units
	 * @Route("/api/infirmary/treat-units/", name="tinfirmary_reat_units", methods={"POST"})
	 * @param Session $session
	 * @param Globals $globals
	 * @param Infirmary $infirmary
	 * @param Point $point
	 * @return JsonResponse
	 * @throws Exception
	 */
	public function treatUnits(Session $session, Globals $globals, Infirmary $infirmary, Point $point): JsonResponse
	{
		$em = $this->getDoctrine()->getManager();
		$now = new \DateTime();
		$infos = $session->get("jwt_infos");
		$unit_array_name = $infos->unit_array_name;
		$number_to_treat = (int)$infos->number_to_recruit;
		$treated_number = 0;

		if ($infirmary->testWithdrawResourcesToTreat($unit_array_name, $number_to_treat)) {
			$units = $em->getRepository(Unit::class)->findBy([
				"base" => $globals->getCurrentBase(),
				"in_treatment" => false,
				"in_recruitment" => false,
				"unitMovement" => null
			], ["id" => "ASC", "life" => "ASC"]);

			/** @var Unit $unit */
			foreach ($units as $unit) {
				if ($treated_number < $number_to_treat) {
					$end_treatment = $now->add(new DateInterval("PT" . $infirmary->getTimeToTreat($unit->getArrayName()) . "S"));
					$unit->setInTreatment(true);
					$unit->setEndTreatment(clone $end_treatment);
					$em->persist($unit);

					$treated_number++;
				} else {
					break;
				}
			}

			$em->flush();

			return new JsonResponse([
				"success" => true,
				"success_message" => "Les unités sélectionnées sont en cours de guérison"
			]);
		} else {
			return new JsonResponse([
				"success" => false,
				"error_message" => "Il n'y a pas assez de ressources dans la base pour soigner autant d'unités"
			]);
		}
	}

	/**
	 * method to send to front current units that are in treatment in base
	 * @Route("/api/infirmary/units-in-treatment/", name="units_in_treatment", methods={"POST"})
	 * @param Session $session
	 * @param Globals $globals
	 * @return JsonResponse
	 */
	public function sendUnitsInTreatment(Session $session, Globals $globals): JsonResponse
	{
		return new JsonResponse([
			"success" => true,
			"units_in_treatment" => $this->getDoctrine()->getRepository(Unit::class)->findByUnitsInTreatment($globals->getCurrentBase()),
			"token" => $session->get("user_token")->getToken(),
		]);
	}

	/**
	 * method to finish current treatments in base
	 * @Route("/api/infirmary/end-treatments-base/", name="infirmary_end_recruitments", methods={"POST"})
	 * @param Session $session
	 * @param Globals $globals
	 * @param Infirmary $infirmary
	 * @return JsonResponse
	 * @throws Exception
	 */
	public function endTreatment(Session $session, Globals $globals, Infirmary $infirmary): JsonResponse
	{
		$infirmary->endTreatmentUnitsInBase();

		return new JsonResponse([
			"success" => true,
			"units_in_treatment" => $this->getDoctrine()->getRepository(Unit::class)->findByUnitsInTreatment($globals->getCurrentBase()),
			"token" => $session->get("user_token")->getToken(),
		]);
	}
}