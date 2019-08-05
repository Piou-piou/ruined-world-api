<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class RankingController extends AbstractController
{
	/**
	 * @param SessionInterface $session
	 * @return JsonResponse
	 */
	public function sendRanks(SessionInterface $session): JsonResponse
	{
		return new JsonResponse();
	}
}