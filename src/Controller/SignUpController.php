<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

class SignUpController extends AbstractController
{

	/**
	 * @Route("/api/signup/check-pseudo-used/", name="signup_check_pseudo_used", methods={"POST"})
	 * @return JsonResponse
	 */
	public function checkIfPseudoIsAvailable(): JsonResponse
	{
		return new JsonResponse([
			"success" => true,
			"used_pseudo" => true
		]);
	}
}