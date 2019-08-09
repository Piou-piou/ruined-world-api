<?php

namespace App\Controller;

use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class SignUpController extends AbstractController
{
	private $error_message = "";

	/**
	 * method to check if pseudo is available
	 * @Route("/api/signup/check-pseudo-used/", name="signup_check_pseudo_used", methods={"POST"})
	 * @Route("/api/signup/check-mail-used/", name="signup_check_mail_used", methods={"POST"})
	 * @param Request $request
	 * @return JsonResponse
	 */
	public function checkIfPseudoIsAvailable(Request $request): JsonResponse
	{
		$user_repo = $this->getDoctrine()->getManager()->getRepository(User::class);

		if ($request->get("pseudo")) {
			$user = $user_repo->findOneBy(["pseudo" => $request->get("pseudo")]);
			$this->error_message = "Ce pseudo est déjà utilisé";
		} else if ($request->get("mail")) {
			$user = $user_repo->findOneBy(["mail" => $request->get("mail")]);
			$this->error_message = "Cette adresse email est déjà utilisé";
		}

		if (!$user) {
			$this->error_message = "";
		}

		return new JsonResponse([
			"success" => true,
			"error_message" => $this->error_message
		]);
	}
}