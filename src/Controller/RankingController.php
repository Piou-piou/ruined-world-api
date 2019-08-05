<?php

namespace App\Controller;

use App\Entity\User;
use App\Service\Globals;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;

class RankingController extends AbstractController
{
	/**
	 * method that send ranked players
	 * @Route("/api/ranking/", name="ranking", methods={"POST"})
	 * @param SessionInterface $session
	 * @param Globals $globals
	 * @return JsonResponse
	 */
	public function sendRanks(SessionInterface $session, Globals $globals): JsonResponse
	{
		$infos = $session->get("jwt_infos");
		$page_number = isset($infos->page_number) ? $infos->page_number : 0;
		$users_per_page = $globals->getGeneralConfig()["rank_user_per_page"];

		$players = $this->getDoctrine()->getRepository(User::class)->findBy([
			"archived" => false
		], [
			"points" => "ASC"
		], $users_per_page, $page_number);

		return new JsonResponse([
			"success" => true,
			"players" => $players
		]);
	}
}