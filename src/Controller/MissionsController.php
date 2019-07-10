<?php

namespace App\Controller;

use App\Entity\Mission;
use App\Entity\Unit;
use App\Service\Globals;
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
		$mission_config = $globals->getMissionsConfig();
		$missions = $globals->getCurrentBase()->getMissions();
		$return_missions = [];

		/** @var Mission $mission */
		foreach ($missions as $mission) {
			$return_missions[$mission->getMissionsConfigId()] = $mission_config[$mission->getMissionsConfigId()];
			$return_missions[$mission->getMissionsConfigId()]["in_progress"] = $mission->getInProgress();
		}

		return new JsonResponse([
			"success" => true,
			"token" => $session->get("user")->getToken(),
			"missions" => $return_missions,
			"units" => $this->getDoctrine()->getRepository(Unit::class)->findByUnitsInBase($globals->getCurrentBase())
		]);
	}

	/**
	 * method to send units in mission
	 * @Route("/api/missions/send-units/", name="missions_send_units", methods={"POST"})
	 * @param SessionInterface $session
	 * @param Globals $globals
	 * @return JsonResponse
	 */
	public function sendUnitsInMission(SessionInterface $session, Globals $globals): JsonResponse
	{
		return new JsonResponse([
			"success" => true,
			"token" => $session->get("user")->getToken(),
		]);
	}
}