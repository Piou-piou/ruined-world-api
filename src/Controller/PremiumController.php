<?php

namespace App\Controller;

use App\Service\Globals;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;

class PremiumController extends AbstractController
{
	/**
	 * ùethod to send config file of premium advantages
	 * @Route("/api/premium/list-advantages/", name="premium_list_advantage", methods={"POST"})
	 * @param SessionInterface $session
	 * @param Globals $globals
	 * @return JsonResponse
	 */
	public function listAdvantages(SessionInterface $session, Globals $globals): JsonResponse
	{
		$premium = $globals->getPremiumConfig();

		return new JsonResponse([
			"success" => true,
			"token" => $session->get("user_token")->getToken(),
			"premium_config" => $premium
		]);
	}
}