<?php

namespace App\Controller;

use App\Service\Globals;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Routing\Annotation\Route;

class BaseController extends AbstractController
{
	/**
	 * @Route("/base/", name="base", methods={"POST"})
	 * @param Session $session
	 * @param Globals $globals
	 * @return JsonResponse
	 * @throws \Exception
	 */
	public function sendInfos(Session $session, Globals $globals): JsonResponse
	{
		$base = $globals->getCurrentBase();
		
		return new JsonResponse([
			"success" => true,
			"token" => $session->get("user")->getToken(),
			"base" => $base
		]);
	}
}