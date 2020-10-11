<?php

namespace App\Controller;

use App\Entity\Building;
use App\Entity\League;
use App\Entity\User;
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

		$league = $em->getRepository(League::class)->findOneBy(["user" => $session->get("user")]);
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
		/** @var User $user */
		$user = $session->get("user");

		if ($infos->league_id) {
			$league = $em->getRepository(League::class)->findOneBy([
				"id" => $infos->league_id,
				"user" => $user
			]);
		} else {
			$league = new League();
			$league->setUser($user);
			$league->setPoints(0);
		}

		$league->setName($infos->name);
		$em->flush();

		return new JsonResponse([
			"success" => true,
			"success_message" => "Ton alliance a été créée",
			"league" => $api->serializeObject($league),
		]);
	}

	/**
	 * @Route("/api/embassy/delete/", name="embassy_delete", methods={"POST"})
	 * @param SessionInterface $session
	 * @param Api $api
	 * @return JsonResponse
	 * @throws AnnotationException
	 * @throws ExceptionInterface
	 */
	public function deleteLeague(SessionInterface $session, Api $api): JsonResponse
	{
		$em = $this->getDoctrine()->getManager();
		$infos = $session->get("jwt_infos");

		$league = $em->getRepository(League::class)->findOneBy([
			"id" => $infos->league_id,
			"user" => $session->get("user")
		]);

		if ($league) {
			$em->remove($league);
			$em->flush();

			return new JsonResponse([
				"success" => true,
				"success_message" => "Ton alliance a été dissoute",
				"league" => null,
			]);
		}

		return new JsonResponse([
			"success" => false,
			"success_message" => "Ton alliance ne peut pas être dissoute",
			"league" => $api->serializeObject($league),
		]);
	}
}