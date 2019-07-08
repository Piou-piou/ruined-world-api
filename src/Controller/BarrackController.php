<?php

namespace App\Controller;

use App\Entity\Unit;
use App\Service\Barrack;
use App\Service\Globals;
use App\Service\Point;
use DateInterval;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Routing\Annotation\Route;

class BarrackController extends AbstractController
{
	/**
	 * method to send units that are possible to recruit
	 * @Route("/api/barrack/list-units-to-recruit/", name="list_units_to_recruit", methods={"POST"})
	 * @param Session $session
	 * @param Globals $globals
	 * @param Barrack $barrack
	 * @return JsonResponse
	 */
	public function sendUnitsPossibleToRecruit(Session $session, Globals $globals, Barrack $barrack): JsonResponse
	{
		return new JsonResponse([
			"success" => true,
			"units" => $barrack->getUnitsPossibleToRecruit(),
			"token" => $session->get("user")->getToken(),
		]);
	}

	/**
	 * method to send to front current units that are in recruitment in base
	 * @Route("/api/barrack/units-in-recruitment/", name="units_in_recruitment", methods={"POST"})
	 * @param Session $session
	 * @param Globals $globals
	 * @return JsonResponse
	 */
	public function sendUnitsInRecruitment(Session $session, Globals $globals): JsonResponse
	{
		return new JsonResponse([
			"success" => true,
			"units_in_recruitment" => $this->getDoctrine()->getRepository(Unit::class)->findByUnitsInRecruitment($globals->getCurrentBase()),
			"token" => $session->get("user")->getToken(),
		]);
	}

	/**
	 * method to finish current recruitments in base
	 * @Route("/api/barrack/end-recruitments-base/", name="barrack_end_recruitments", methods={"POST"})
	 * @param Session $session
	 * @param Globals $globals
	 * @param Barrack $barrack
	 * @return JsonResponse
	 * @throws Exception
	 */
	public function endRecruitment(Session $session, Globals $globals, Barrack $barrack): JsonResponse
	{
		$barrack->endRecruitmentUnitsInBase();

		return new JsonResponse([
			"success" => true,
			"units_in_recruitment" => $this->getDoctrine()->getRepository(Unit::class)->findByUnitsInRecruitment($globals->getCurrentBase()),
			"token" => $session->get("user")->getToken(),
		]);
	}

	/**
	 * method to create unit and set time of their recruitment
	 * @Route("/api/barrack/recruit-units/", name="recruit_units", methods={"POST"})
	 * @param Session $session
	 * @param Globals $globals
	 * @param Barrack $barrack
	 * @param Point $point
	 * @return JsonResponse
	 * @throws Exception
	 */
	public function recruitUnits(Session $session, Globals $globals, Barrack $barrack, Point $point): JsonResponse
	{
		$em = $this->getDoctrine()->getManager();
		$now = new \DateTime();
		$infos = $session->get("jwt_infos");
		$unit_array_name = $infos->unit_array_name;
		$number_to_recruit = (int)$infos->number_to_recruit;
		$unit_config = $globals->getUnitsConfig()[$unit_array_name];
		$end_recruitment = $now->add(new DateInterval("PT" . $barrack->getTimeToRecruit($unit_config["recruitment_time"]) . "S"));

		if ($barrack->testWithdrawResourcesToRecruit($unit_array_name, $number_to_recruit)) {
			for ($i = 0; $i < $number_to_recruit; $i++) {
				$unit = new Unit();
				$unit->setName($unit_config["name"]);
				$unit->setArrayName($unit_config["array_name"]);
				$unit->setAssaultLevel(1);
				$unit->setDefenseLevel(1);
				$unit->setBase($globals->getCurrentBase());
				$unit->setInRecruitment(true);
				$unit->setEndRecruitment($end_recruitment);
				$em->persist($unit);
				$point->addPoints("end_unit_recruitment");
			}

			$em->flush();

			return new JsonResponse([
				"success" => true,
				"success_message" => "Les unités demandées sont en cours de recrutement"
			]);
		} else {
			return new JsonResponse([
				"success" => false,
				"error_message" => "Il n'y a pas assez de ressources dans la base pour recruter autant d'unités"
			]);
		}
	}
}