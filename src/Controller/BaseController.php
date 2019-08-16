<?php

namespace App\Controller;

use App\Entity\Base;
use App\Service\Api;
use App\Service\Globals;
use App\Service\Resources;
use Doctrine\Common\Annotations\AnnotationException;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Exception\ExceptionInterface;

class BaseController extends AbstractController
{
	/**
	 * method that send the main base of a user if no token of guid_base set in front
	 * @Route("/api/main-base/", name="main_base", methods={"POST"})
	 * @param Session $session
	 * @return JsonResponse
	 */
	public function getMainBase(Session $session): JsonResponse
	{
		$main_base = $this->getDoctrine()->getRepository(Base::class)->findOneBy([
			"user" => $session->get("user"),
			"archived" => false,
		], ["id" => "asc"]);
		$guid_base = null;
		$success = false;
		
		if ($main_base) {
			$guid_base = $main_base->getGuid();
			$success = true;
		}
		
		return new JsonResponse([
			"success" => $success,
			"guid_base" => $guid_base,
			"token" => $session->get("user_token")->getToken(),
		]);
	}

	/**
	 * method that send all infos about the current base
	 * @Route("/api/base/", name="base", methods={"POST"})
	 * @param Session $session
	 * @param Globals $globals
	 * @param Api $api
	 * @param Resources $resources
	 * @return JsonResponse
	 * @throws AnnotationException
	 * @throws ExceptionInterface
	 */
	public function sendInfosCurrentBase(Session $session, Globals $globals, Api $api, Resources $resources): JsonResponse
	{
		$base = $globals->getCurrentBase();
		
		if ($base === false) {
			return new JsonResponse([
				"success" => false,
			]);
		}
		
		return new JsonResponse([
			"success" => true,
			"base" => $api->serializeObject($base),
			"resources_infos" => [
				"max_storage_wharehouse" => $resources->getWarehouseCapacity(),
				"max_storage_garner" => $resources->getGarnerCapacity(),
				"electricity_production" => $resources->getElectricityProduction(),
				"iron_production" => $resources->getIronProduction(),
				"fuel_production" => $resources->getFuelProduction(),
				"water_production" => $resources->getWaterProduction(),
			],
			"premium_storage" => $resources->getFullStorageInHour(),
			"token" => $session->get("user_token")->getToken(),
		]);
	}

	/**
	 * @Route("/api/base/player/", name="base_player", methods={"POST"})
	 * @param Session $session
	 * @param Globals $globals
	 * @param Api $api
	 * @return JsonResponse
	 * @throws AnnotationException
	 * @throws ExceptionInterface
	 * @throws Exception
	 */
	public function sendInfosAboutABase(Session $session, Globals $globals, Api $api): JsonResponse
	{
		$infos = $session->get("jwt_infos");
		$base = $this->getDoctrine()->getRepository(Base::class)->findOneBy(["guid" => $infos->guid_other_base]);

		if ($base) {
			return new JsonResponse([
				"success" => true,
				"base" => $api->serializeObject($base),
				"can_attack" => $globals->canAttackPlayer($base->getUser()),
				"travel_time" => $globals->getTimeToTravel($globals->getCurrentBase(), $base, 1, true),
				"token" => $session->get("user_token")->getToken(),
			]);
		} else {
			return new JsonResponse([
				"success" => false,
				"error_message" => "Aucune base n'existe à ces positions",
				"token" => $session->get("user_token")->getToken(),
			]);
		}
	}

	/**
	 * method that send actual resources of the base
	 * @Route("/api/refresh-resources/", name="refresh_resources", methods={"POST"})
	 * @param Session $session
	 * @param Globals $globals
	 * @param Resources $resources
	 * @return JsonResponse
	 */
	public function sendResources(Session $session, Globals $globals, Resources $resources): JsonResponse
	{
		$base = $globals->getCurrentBase();
		
		return new JsonResponse([
			"electricity" => $base->getElectricity(),
			"iron" => $base->getIron(),
			"fuel" => $base->getFuel(),
			"water" => $base->getWater(),
			"food" => $base->getFood(),
			"premium_storage" => $resources->getFullStorageInHour(),
			"token" => $session->get("user_token")->getToken(),
		]);
	}

	/**
	 * method that change the current base name
	 * @Route("/api/base/change-name/", name="change_base_name", methods={"POST"})
	 * @param Session $session
	 * @param Globals $globals
	 * @return JsonResponse
	 */
	public function changeName(Session $session, Globals $globals): JsonResponse
	{
		$base = $globals->getCurrentBase();
		$em = $this->getDoctrine()->getManager();
		$infos = $session->get("jwt_infos");
		$base_name = isset($infos->base_name) && $infos->base_name ? $infos->base_name : null;
		$return_infos = [
			"success" => false,
			"error_message" => "Le nom de la base ne peut pas être vide",
			"token" => $session->get("user_token")->getToken(),
		];

		if ($base_name) {
			$base_exist = $em->getRepository(Base::class)->findByBaseNameExist($base, $base_name);

			if (count($base_exist) > 0) {
				$return_infos = [
					"success" => false,
					"error_message" => "Une base existe déjà avec ce nom, merci d'en choisir un autre",
					"token" => $session->get("user_token")->getToken(),
				];
			} else {
				if (strlen($base_name) > 20) {
					$return_infos = [
						"success" => false,
						"error_message" => "Le nom de la base ne doit pas exéder 20 catactères",
						"token" => $session->get("user_token")->getToken(),
					];
				} else {
					$base->setName($base_name);
					$em->persist($base);
					$em->flush();
					$return_infos = [
						"success" => true,
						"success_message" => "Le nom de la base a été changé",
						"base_name" => $base_name,
						"token" => $session->get("user_token")->getToken(),
					];
				}
			}
		}

		return new JsonResponse($return_infos);
	}

	/**
	 * @Route("/api/bases-map/", name="bases_map", methods={"POST"})
	 * @param Session $session
	 * @return JsonResponse
	 */
	public function sendBasesForMap(Session $session): JsonResponse
	{
		$em = $this->getDoctrine()->getManager();
		$bases = $em->getRepository(Base::class)->findByBasesForMap();
		$player_bases = $em->getRepository(Base::class)->findBy(["user" => $session->get("user"), "archived" => false]);
		$guids_player_bases = [];

		foreach ($player_bases as $player_base) {
			$guids_player_bases[] = $player_base->getGuid();
		}
		
		return new JsonResponse([
			"success" => true,
			"guids_player_bases" => $guids_player_bases,
			"bases" => $bases,
			"token" => $session->get("user_token")->getToken(),
		]);
	}

	/**
	 * method that send time to travel between current base of a player and an other base
	 * @Route("/api/base/travel-time/", name="base_travel_time", methods={"POST"})
	 * @param Session $session
	 * @param Globals $globals
	 * @return JsonResponse
	 */
	public function sendTimeToTravelToBase(Session $session, Globals $globals): JsonResponse
	{
		$em = $this->getDoctrine()->getManager();
		$base = $globals->getCurrentBase();
		$infos = $session->get("jwt_infos");

		$other_base = $em->getRepository(Base::class)->findOneBy(["guid" => $infos->guid_other_base]);
		$travel_time = $globals->getTimeToTravel($base, $other_base, 1, true);

		return new JsonResponse([
			"success" => true,
			"travel_time" => $travel_time,
			"token" => $session->get("user_token")->getToken(),
		]);
	}
}