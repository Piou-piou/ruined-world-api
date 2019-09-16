<?php

namespace App\Controller;

use App\Entity\Building;
use App\Service\Api;
use App\Service\Globals;
use App\Service\Point;
use App\Service\Resources;
use DateInterval;
use DateTime;
use Doctrine\Common\Annotations\AnnotationException;
use Doctrine\ORM\NonUniqueResultException;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Exception\ExceptionInterface;

class BuildingController extends AbstractController
{
	/**
	 * method to build or upgrade a building of a base
	 * @Route("/api/buildings/build/", name="build_building", methods={"POST"})
	 * @param SessionInterface $session
	 * @param Globals $globals
	 * @param \App\Service\Building $building_service
	 * @param Point $point
	 * @return JsonResponse
	 * @throws NonUniqueResultException
	 * @throws Exception
	 */
	public function buildOrUpgrade(SessionInterface $session, Globals $globals, \App\Service\Building $building_service, Point $point): JsonResponse
	{
		$em = $this->getDoctrine()->getManager();
		$infos = $session->get("jwt_infos");
		$now = new DateTime();
		$building_config = $globals->getBuildingsConfig()[$infos->array_name];
		$base = $globals->getCurrentBase();
		$buildings_in_construction = $em->getRepository(Building::class)->finByBuildingInConstruction($base);
		/** @var Building $last_construction_building */
		$last_construction_building = count($buildings_in_construction) ? end($buildings_in_construction) : null;
		
		/**
		 * @var $building Building
		 */
		$building = $em->getRepository(Building::class)->findByBuildingInBase($infos->array_name, $base);
		
		if (!$building) {
			$building = new Building();
			$building->setName($building_config["name"]);
			$building->setArrayName($infos->array_name);
			$building->setLocation($infos->case);
			$building->setBase($base);
		}

		if ($building->getLevel() === $building_config["max_level"]) {
			return new JsonResponse([
				"success" => false,
				"error_message" => "Ce batiment a atteint son niveau maximum",
				"token" => $session->get("user_token")->getToken(),
			]);
		}
		
		if (count($buildings_in_construction) >= $globals->getMaxConstructionInConstructionWaiting()) {
			return new JsonResponse([
				"success" => false,
				"error_message" => "La file d'attente des constructions est pleine",
				"token" => $session->get("user_token")->getToken(),
			]);
		}
		
		$building->setInConstruction(true);
		$end_construction = $now->add(new DateInterval("PT" . $building_service->getConstructionTime($infos->array_name, $building->getLevel()) . "S"));
		$building->setEndConstruction($end_construction);

		if ($last_construction_building && $last_construction_building->getArrayName() === $building->getArrayName()) {
			return new JsonResponse([
				"success" => false,
				"error_message" => "Ce batiment est déjà dans la file",
				"token" => $session->get("user_token")->getToken(),
			]);
		} else if ($last_construction_building) {
			$building->setStartConstruction($last_construction_building->getEndConstruction());
			$end_construction = clone $last_construction_building->getEndConstruction();
			$end_construction = $end_construction->add(new DateInterval("PT" . $building_service->getConstructionTime($infos->array_name, $building->getLevel()) . "S"));
			$building->setEndConstruction($end_construction);
		}
		
		if ($building_service->testWithdrawResourcesToBuild($infos->array_name) === false) {
			return new JsonResponse([
				"success" => false,
				"error_message" => "Vous n'avez pas assez de ressouces",
				"token" => $session->get("user_token")->getToken(),
			]);
		}
		
		$em->persist($building);
		$em->flush();
		$point->addPoints("building_construction");
		
		return new JsonResponse([
			"success" => true,
			"token" => $session->get("user_token")->getToken(),
		]);
	}

	/**
	 * method to send detail infos about a building
	 * @Route("/api/buildings/show/", name="building_show", methods={"POST"})
	 * @param SessionInterface $session
	 * @param Globals $globals
	 * @param Api $api
	 * @param Resources $resources
	 * @param \App\Service\Building $building_service
	 * @return JsonResponse
	 * @throws NonUniqueResultException
	 * @throws AnnotationException
	 * @throws ExceptionInterface
	 */
	public function sendBuildingInfo(SessionInterface $session, Globals $globals, Api $api, Resources $resources, \App\Service\Building $building_service): JsonResponse
	{
		$em = $this->getDoctrine()->getManager();
		$infos = $session->get("jwt_infos");
		$buildings_config = $globals->getBuildingsConfig();
		$building = $em->getRepository(Building::class)->findByBuildingInBase($infos->array_name, $globals->getCurrentBase());

		if (!$building) {
			return new JsonResponse([
				"success" => false,
				"error_message" => "Ce bâtiment n'existe pas dans votre base",
				"token" => $session->get("user_token")->getToken(),
			]);
		}

		$explanation_string = $building_service->getExplanationStringPower($infos->array_name, $building->getLevel());

		return new JsonResponse([
			"success" => true,
			"building" => $api->serializeObject($building),
			"explanation" => $buildings_config[$infos->array_name]["explanation"],
			"explanation_current_power" => $explanation_string["current"],
			"explanation_next_power" => $explanation_string["next"],
			"construction_time" => $building_service->getConstructionTime($infos->array_name, $building->getLevel()),
			"resources_build" => $resources->getResourcesToBuild($infos->array_name),
			"token" => $session->get("user_token")->getToken(),
			"premium_when_upgrade" => $building_service->getWhenIsPossibleToUpgrade($infos->array_name)
		]);
	}

	/**
	 * method to send in construction buildings in base
	 * @Route("/api/buildings/in-construction/", name="building_in_construction", methods={"POST"})
	 * @param Session $session
	 * @param Globals $globals
	 * @return JsonResponse
	 * @throws Exception
	 */
	public function sendInConstructionBuildingsBase(Session $session,Globals $globals): JsonResponse
	{
		$em = $this->getDoctrine()->getManager();
		$buildings = $em->getRepository(Building::class)->finByBuildingInConstruction($globals->getCurrentBase());
		$return_buildings = [];
		
		if (count($buildings) > 0) {
			/** @var Building $building */
			foreach ($buildings as $building) {
				$return_buildings[] = [
					"id" => $building->getId(),
					"name" => $building->getName(),
					"startConstruction" => $building->getStartConstruction() ? $building->getStartConstruction()->getTimestamp() : null,
					"endConstruction" => $building->getEndConstruction()->getTimestamp()
				];
			}
		}
		
		return new JsonResponse([
			"success" => true,
			"buildings" => $return_buildings,
			"token" => $session->get("user_token")->getToken(),
		]);
	}

	/**
	 * method to finish current constructions in base
	 * @Route("/api/buildings/end-constructions-base/", name="building_end_constructions", methods={"POST"})
	 * @param Session $session
	 * @param Globals $globals
	 * @param \App\Service\Building $building
	 * @return JsonResponse
	 * @throws Exception
	 */
	public function endConstructions(Session $session, Globals $globals, \App\Service\Building $building): JsonResponse
	{
		$building->endConstructionBuildingsInBase();

		return $this->sendInConstructionBuildingsBase($session, $globals);
	}

	/**
	 * method that send all building that are possible to build
	 * @Route("/api/buildings/list-to-build/", name="list_building_to_build", methods={"POST"})
	 * @param Globals $globals
	 * @param \App\Service\Building $building_service
	 * @param Resources $resources
	 * @param Session $session
	 * @return JsonResponse
	 */
	public function sendBuildingToBuild(Globals $globals, \App\Service\Building $building_service, Resources $resources, Session $session): JsonResponse
	{
		$em = $this->getDoctrine()->getManager();
		$buildings = $em->getRepository(Building::class)->finByBuildingArrayNameInBase($globals->getCurrentBase());
		$buildings_config = $globals->getBuildingsConfig();
		$return_buildings = [];
		
		if (count($buildings) > 0) {
			foreach ($buildings_config as $building_config) {
				$array_name = $building_config["array_name"];
				$explanation_string = $building_service->getExplanationStringPower($array_name, 0);
				
				if (!array_key_exists($array_name, $buildings)) {
					if (count($building_config["to_build"]) === 0) {
						$return_buildings[$array_name] = [
							"name" => $building_config["name"],
							"array_name" => $array_name,
							"explanation" => $building_config["explanation"],
							"explanation_current_power" => $explanation_string["current"],
							"explanation_next_power" => $explanation_string["next"],
							"construction_time" => $building_service->getConstructionTime($array_name, 0),
							"resources_build" => $resources->getResourcesToBuild($array_name)
						];
					} else {
						$add_building = true;
						foreach ($building_config["to_build"] as $key_build => $to_build) {
							if (!array_key_exists($key_build, $buildings) || $buildings[$key_build] < $to_build) {
								$add_building = false;
							}
						}
						
						if ($add_building === true) {
							$return_buildings[$array_name] = [
								"name" => $building_config["name"],
								"array_name" => $array_name,
								"explanation" => $building_config["explanation"],
								"explanation_current_power" => $explanation_string["current"],
								"explanation_next_power" => $explanation_string["next"],
								"construction_time" => $building_service->getConstructionTime($array_name, 0),
								"resources_build" => $resources->getResourcesToBuild($array_name)
							];
						}
					}
				}
			}
		}
		
		return new JsonResponse([
			"success" => true,
			"buildings" => $return_buildings,
			"nb_buildings" => count($return_buildings),
			"token" => $session->get("user_token")->getToken(),
		]);
	}
}