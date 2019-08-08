<?php

namespace App\Controller;

use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class SignUpController extends AbstractController
{
	/**
	 * method to check if pseudo is available
	 * @Route("/api/signup/check-pseudo-used/", name="signup_check_pseudo_used", methods={"POST"})
	 * @param Request $request
	 * @return JsonResponse
	 */
	public function checkIfPseudoIsAvailable(Request $request): JsonResponse
	{
		$user = $this->getDoctrine()->getManager()->getRepository(User::class)->findOneBy(["pseudo" => $request->get("pseudo")]);
		$error_message = "";

		if ($user) {
			$error_message = "Ce pseudo est déjà utilisé, merci d'en choisir un autre";
		}

		return new JsonResponse([
			"success" => true,
			"error_message" => $error_message
		]);
	}
}