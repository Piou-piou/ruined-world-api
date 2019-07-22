<?php

namespace App\Controller;

use App\Entity\Base;
use App\Entity\UnitMovement;
use App\Service\Unit;
use Doctrine\DBAL\DBALException;
use Doctrine\ORM\NonUniqueResultException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;

class FightController extends AbstractController
{
	/**
	 * @Route("/api/fight/send-attack/", name="fight_send_attack", methods={"POST"})
	 * @param SessionInterface $session
	 * @param Unit $unit
	 * @param \App\Service\UnitMovement $unit_movement_service
	 * @return JsonResponse
	 * @throws DBALException
	 * @throws NonUniqueResultException
	 */
	public function sendUnitsToAttack(SessionInterface $session, Unit $unit, \App\Service\UnitMovement $unit_movement_service): JsonResponse
	{
		$em = $this->getDoctrine()->getManager();
		$infos = $session->get("jwt_infos");
		$success = true;
		$error_message = "";

		$dest_base = $em->getRepository(Base::class)->findOneBy(["guid" => $infos->guid_dest_base]);
		if (!$dest_base) {
			$success = false;
			$error_message = "Impossible de trouver la base demandée";
		}

		if ($unit->testEnoughUnitInBaseToSend((array)$infos->units) === false) {
			$success = false;
			$error_message = "Vous n'avez pas autant d'unités à envoyer en mission";
		}

		if ($success === true) {
			$unit_movement = $unit_movement_service->create(UnitMovement::TYPE_ATTACK,  $dest_base->getId(), UnitMovement::MOVEMENT_TYPE_GO);
			$unit->putUnitsInMovement((array)$infos->units, $unit_movement);
		}

		return new JsonResponse([
			"success" => $success,
			"token" => $session->get("user")->getToken(),
			"error_message" => $error_message,
			"success_message" => "Vos unités se mettent en route"
		]);
	}
}