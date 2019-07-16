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
			"token" => $session->get("user")->getToken(),
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
	 * @return JsonResponse
	 * @throws NonUniqueResultException
	 * @throws DBALException
	 */
	public function sendUnitsInMission(SessionInterface $session, \App\Service\Unit $unit, \App\Service\UnitMovement $unit_movement_service): JsonResponse
	{
		$em = $this->getDoctrine()->getManager();
		$infos = $session->get("jwt_infos");
		$success = true;
		$error_message = "";

		/** @var Mission $mission */
		$mission = $em->getRepository(Mission::class)->findOneBy(["missions_config_id" => $infos->mission_id]);
		if (!$mission) {
			$success = false;
			$error_message = "Impossible de trouver la mission demandée";
		}
		if ($unit->testEnoughUnitInBaseToSend((array)$infos->units) === false) {
			$success = false;
			$error_message = "Vous n'avez pas autant d'unités à envoyer en mission";
		}

		if ($success === true) {
			$unit_movement = $unit_movement_service->create(UnitMovement::TYPE_MISSION, $mission->getMissionsConfigId(), $mission->getId(), UnitMovement::MOVEMENT_TYPE_MISSION);
			$unit->putUnitsInMovement((array)$infos->units, $unit_movement);

			$mission->setUnitMovement($unit_movement);
			$mission->setInProgress(true);
			$em->persist($mission);
			$em->flush();
		}

		return new JsonResponse([
			"success" => $success,
			"token" => $session->get("user")->getToken(),
			"error_message" => $error_message,
			"success_message" => "Vos unités se mettent en route"
		]);
	}

	/**
	 * @Route("/api/missions/update-movements/", name="missions_update_movements", methods={"POST"})
	 * @param SessionInterface $session
	 * @param Globals $globals
	 * @param \App\Service\UnitMovement $unit_movement
	 * @return JsonResponse
	 * @throws \Exception
	 */
	public function updateUnitMovements(SessionInterface $session, Globals $globals, \App\Service\UnitMovement $unit_movement): JsonResponse
	{
		$unit_movement->updateUnitMovement($globals->getCurrentBase(true));

		return new JsonResponse([
			"success" => true,
			"token" => $session->get("user")->getToken()
		]);
	}
}