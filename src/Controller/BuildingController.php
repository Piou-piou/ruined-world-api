<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;

class BuildingController extends AbstractController
{
	/**
	 * @Route("/buildings/build/", name="build_building", methods={"POST"})
	 * @param SessionInterface $session
	 * @return JsonResponse
	 */
	public function buildOrUpgrade(SessionInterface $session): JsonResponse
	{
		return new JsonResponse([
			"success" => true,
			"token" => $session->get("user")->getToken()
		]);
	}
}