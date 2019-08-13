<?php

namespace App\Controller;

use App\Entity\Mission;
use App\Entity\Unit;
use App\Entity\UnitMovement;
use App\Service\Globals;
use Doctrine\DBAL\DBALException;
use Doctrine\ORM\NonUniqueResultException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;

class MissionsController extends AbstractController
{
	/**
	 * method that return current missions available in the base
	 * @Route("/api/missions/list/", name="missions_list", methods={"POST"})
	 * @param Session $session
	 * @param Globals $globals
	 * @return JsonResponse
	 */
	public function sendCurrentMissions(Session $session, Globals $globals): JsonResponse
	{
		$em = $this->getDoctrine()->getManager();
		$mission_config = $globals->getMissionsConfig();
		$missions = $em->getRepository(Mission::class)->findByMissionAvailable($globals->getCurrentBase());
		$return_missions = [];

		/** @var Mission $mission */
		foreach ($missions as $mission) {
			$return_missions[$mission->getMissionsConfigId()] = $mission_config[$mission->getMissionsConfigId()];
			$return_missions[$mission->getMissionsConfigId()]["in_progress"] = $mission->getInProgress();
			$return_missions[$mission->getMissionsConfigId()]["id"] = $mission->getMissionsConfigId();
		}

		return new JsonResponse([
			"success" => true,
			"token" => $session->get("user_token")->getToken(),
			"missions" => $return_missions,
			"units" => $em->getRepository(Unit::class)->findByUnitsInBase($globals->getCurrentBase())
		]);
	}

	/**
	 * method to send units in mission
	 * @Route("/api/missions/send-units/", name="missions_send_units", methods={"POST"})
	 * @param SessionInterface $session
	 * @param \App\Service\Unit $unit
	 * @param \App\Service\UnitMovement $unit_movement_service
	 * @param Globals $globals
	 * @return JsonResponse
	 * @throws DBALException
	 * @throws NonUniqueResultException
	 */
	public function sendUnitsInMission(SessionInterface $session, \App\Service\Unit $unit, \App\Service\UnitMovement $unit_movement_service, Globals $globals): JsonResponse
	{
		$em = $this->getDoctrine()->getManager();
		$infos = $session->get("jwt_infos");
		$success = true;
		$error_message = "";

		/** @var Mission $mission */
		$mission = $em->getRepository(Mission::class)->findOneBy([
			"missions_config_id" => $infos->mission_id,
			"base" => $globals->getCurrentBase()
		]);
		if (!$mission) {
			$success = false;
			$error_message = "Impossible de trouver la mission demandée";
		} else if (count((array)$infos->units)  > 0 && $unit->testEnoughUnitInBaseToSend((array)$infos->units) === false) {
			$success = false;
			$error_message = "Vous n'avez pas autant d'unités à envoyer en mission";
		} else if (count((array)$infos->units) === 0) {
			$success = false;
			$error_message = "Vous devez envoyer au moins une unité en mission";
		}

		if ($success === true) {
			$unit_movement = $unit_movement_service->create(UnitMovement::TYPE_MISSION, $mission->getId(), UnitMovement::MOVEMENT_TYPE_MISSION, $mission->getMissionsConfigId());
			$unit->putUnitsInMovement((array)$infos->units, $unit_movement);

			$mission->setUnitMovement($unit_movement);
			$mission->setInProgress(true);
			$em->persist($mission);
			$em->flush();
		}

		return new JsonResponse([
			"success" => $success,
			"token" => $session->get("user_token")->getToken(),
			"error_message" => $error_message,
			"success_message" => "Vos unités se mettent en route"
		]);
	}
}