<?php

namespace App\Controller;

use App\Entity\User;
use App\Service\Api;
use App\Service\Globals;
use Doctrine\Common\Annotations\AnnotationException;
use Doctrine\ORM\NonUniqueResultException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Exception\ExceptionInterface;

class RankingController extends AbstractController
{
	/**
	 * method that send ranked players
	 * @Route("/api/ranking/", name="ranking", methods={"POST"})
	 * @param SessionInterface $session
	 * @param Globals $globals
	 * @param Api $api
	 * @return JsonResponse
	 * @throws NonUniqueResultException
	 * @throws AnnotationException
	 * @throws ExceptionInterface
	 */
	public function sendRanks(SessionInterface $session, Globals $globals, Api $api): JsonResponse
	{
		$em = $this->getDoctrine()->getManager();
		$infos = $session->get("jwt_infos");
		$users_per_page = $globals->getGeneralConfig()["rank_user_per_page"];
		$max_pages = floor($em->getRepository(User::class)->findByCountRankedPlayers() / $users_per_page);
		$max_pages = $max_pages < 0 ? 0 : $max_pages;
		$page_number = isset($infos->page_number) ? $infos->page_number : $max_pages;
		$offset = $page_number*$users_per_page;

		$players = $em->getRepository(User::class)->findByRank($users_per_page, $offset);

		return new JsonResponse([
			"success" => true,
			"players" => $api->serializeObject($players),
			"max_pages" => $max_pages,
			"current_page" => $page_number
		]);
	}
}