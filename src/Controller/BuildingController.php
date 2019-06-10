<?php

namespace App\Controller;

use App\Entity\Building;
use App\Service\Api;
use App\Service\Globals;
use App\Service\Resources;
use Doctrine\ORM\NonUniqueResultException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;

class BuildingController extends AbstractController
{
	/**
	 * method to build or upgrade a building of a base
	 * @Route("/api/buildings/build/", name="build_building", methods={"POST"})
	 * @param SessionInterface $session
	 * @param Globals $globals
	 * @param \App\Service\Building $building_service
	 * @return JsonResponse
	 * @throws NonUniqueResultException
	 * @throws \Exception
	 */
	public function buildOrUpgrade(SessionInterface $session, Globals $globals, \App\Service\Building $building_service): JsonResponse
	{
		$em = $this->getDoctrine()->getManager();
		$infos = $session->get("jwt_infos");
		$now = new \DateTime();
		$building_config = $globals->getBuildingsConfig()[$infos->array_name];
		$base = $globals->getCurrentBase();
		$buildings_in_construction = $em->getRepository(Building::class)->finByBuildingInConstruction($base);
		
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
		
		if (count($buildings_in_construction) > 0) {
			return new JsonResponse([
				"success" => false,
				"message" => "A building is already in construction in your base.",
				"token" => $session->get("user")->getToken(),
			]);
		}
		
		$building->setInConstruction(true);
		$end_construction = $now->add(new \DateInterval("PT" . $building_service->getConstructionTime($infos->array_name, $building->getLevel()) . "S"));
		$building->setEndConstruction($end_construction);
		
		if ($building_service->testWithdrawResourcesToBuild($infos->array_name) === false) {
			return new JsonResponse([
				"success" => false,
				"message" => "You haven't enough resources",
				"token" => $session->get("user")->getToken(),
			]);
		}
		
		$em->persist($building);
		$em->flush();
		
		return new JsonResponse([
			"success" => true,
			"token" => $session->get("user")->getToken(),
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
	 */
	public function sendBuildingInfo(SessionInterface $session, Globals $globals, Api $api, Resources $resources, \App\Service\Building $building_service): JsonResponse
	{
		$em = $this->getDoctrine()->getManager();
		$infos = $session->get("jwt_infos");
		$building = $em->getRepository(Building::class)->findByBuildingInBase($infos->array_name, $globals->getCurrentBase());

		if (!$building) {
			return new JsonResponse([
				"success" => false,
				"message" => "This building doesn't exist in your base.",
				"token" => $session->get("user")->getToken(),
			]);
		}

		return new JsonResponse([
			"building" => $api->serializeObject($building),
			"construction_time" => $building_service->getConstructionTime($infos->array_name, $building->getLevel()),
			"resources_build" => $resources->getResourcesToBuild($infos->array_name)
		]);
	}
	
	/**
	 * method to send in construction buildings in base
	 * @Route("/api/buildings/in-construction/", name="building_in_construction", methods={"POST"})
	 * @param Globals $globals
	 * @return JsonResponse
	 * @throws \Exception
	 */
	public function sendInConstructionBuildingsBase(Globals $globals): JsonResponse
	{
		$em = $this->getDoctrine()->getManager();
		$buildings = $em->getRepository(Building::class)->finByBuildingInConstruction($globals->getCurrentBase());
		$return_buildings = [];
		
		if (count($buildings) > 0) {
			/** @var Building $building */
			foreach ($buildings as $building) {
				$return_buildings[] = [
					"name" => $building->getName(),
					"endConstruction" => $building->getEndConstruction()->getTimestamp()
				];
			}
		}
		
		return new JsonResponse([
			"success" => true,
			"buildings" => $return_buildings,
		]);
	}
	
	/**
	 * @Route("/api/buildings/list-to-build/", name="list_building_to_build", methods={"POST"})
	 * @param \App\Service\Building $building_service
	 * @param Globals $globals
	 * @return JsonResponse
	 */
	public function sendBuildingToBuild(Globals $globals, \App\Service\Building $building_service, Resources $resources): JsonResponse
	{
		$em = $this->getDoctrine()->getManager();
		$buildings = $em->getRepository(Building::class)->finByBuildingArrayNameInBase($globals->getCurrentBase());
		$buildings_config = $globals->getBuildingsConfig();
		$return_buildings = [];
		
		if (count($buildings) > 0) {
			foreach ($buildings_config as $building_config) {
				$array_name = $building_config["array_name"];
				
				if (!array_key_exists($array_name, $buildings)) {
					if (count($building_config["to_build"]) === 0) {
						$return_buildings[$array_name] = [
							"name" => $building_config["name"],
							"array_name" => $array_name,
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
							$return_buildings[$array_name] = $building_config;
						}
					}
				}
			}
		}
		
		return new JsonResponse([
			"success" => true,
			"buildings" => $return_buildings,
			"nb_buildings" => count($return_buildings)
		]);
	}
}