<?php

namespace App\Controller;

use App\Entity\League;
use App\Service\Api;
use App\Service\Globals;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;

class EmbassyController extends AbstractController
{
	/**
	 * @Route("/api/embassy/show/", name="embassy_show", methods={"POST"})
	 * @param SessionInterface $session
	 * @return JsonResponse
	 */
	public function sendMyEmbassy(SessionInterface $session)
	{
		$em = $this->getDoctrine()->getManager();

		$league = $em->getRepository(League::class)->findOneBy(["leader" => $session->get("user")]);

		return new JsonResponse([
			"success" => true,
			"token" => $session->get("user_token")->getToken(),
			"league" => $league
		]);
	}
}