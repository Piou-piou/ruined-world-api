<?php

namespace App\Controller;

use App\Entity\Building;
use App\Entity\League;
use App\Service\Api;
use App\Service\Globals;
use Doctrine\Common\Annotations\AnnotationException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Exception\ExceptionInterface;

class EmbassyController extends AbstractController
{
	/**
	 * @Route("/api/embassy/show/", name="embassy_show", methods={"POST"})
	 * @param SessionInterface $session
	 * @param Api $api
	 * @param Globals $globals
	 * @return JsonResponse
	 * @throws AnnotationException
	 * @throws ExceptionInterface
	 */
	public function sendMyEmbassy(SessionInterface $session, Api $api, Globals $globals): JsonResponse
	{
		$em = $this->getDoctrine()->getManager();

		$league = $em->getRepository(League::class)->findOneBy(["leader" => $session->get("user")]);
		$embassy = $em->getRepository(Building::class)->findOneBy([
			"array_name" => "embassy",
			"base" => $globals->getCurrentBase()
		]);

		return new JsonResponse([
			"success" => true,
			"token" => $session->get("user_token")->getToken(),
			"league" => $api->serializeObject($league),
			"embassy" => $api->serializeObject($embassy)
		]);
	}

	/**
	 * @Route("/api/embassy/edit/", name="embassy_edit", methods={"POST"})
	 * @param SessionInterface $session
	 * @param Api $api
	 * @return JsonResponse
	 * @throws AnnotationException
	 * @throws ExceptionInterface
	 */
	public function editLeague(SessionInterface $session, Api $api): JsonResponse
	{
		$em = $this->getDoctrine()->getManager();
		$infos = $session->get("jwt_infos");

		if ($infos->league_id) {
			$league = $em->getRepository(League::class)->findOneBy([
				"id" => $infos->league_id,
				"leader" => $session->get("user")
			]);
		} else {
			$league = new League();
			$league->setLeader($session->get("user"));
			$league->setPoints(0);
		}

		$league->setName($infos->name);
		$em->persist($league);
		$em->flush();

		return new JsonResponse([
			"success" => true,
			"success_message" => "Ton alliance a été créée",
			"league" => $api->serializeObject($league),
		]);
	}
}