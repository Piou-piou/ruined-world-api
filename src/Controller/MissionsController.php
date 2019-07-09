<?php

namespace App\Controller;

use App\Entity\Mission;
use App\Service\Globals;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Session\Session;
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
			"missions" => $return_missions
		]);
	}
}