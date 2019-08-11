<?php

namespace App\Controller;

use App\Entity\Base;
use App\Entity\Building;
use App\Entity\User;
use App\Service\Api;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\PasswordEncoderInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

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

	/**
	 * method to register a user, create his base with command center
	 * @Route("/api/signup/register/", name="signup_register", methods={"POST"})
	 * @param Request $request
	 * @param UserPasswordEncoderInterface $password_encoder
	 * @param Api $api
	 * @return JsonResponse
	 * @throws \Exception
	 */
	public function registerUser(Request $request, UserPasswordEncoderInterface $password_encoder, Api $api)
	{
		$em = $this->getDoctrine()->getManager();
		$pseudo = $request->get("pseudo");
		$mail = $request->get("mail");
		$password = $request->get("password");
		$user_repo = $em->getRepository(User::class);

		if (!$pseudo || !$mail || !$password) {
			return new JsonResponse([
				"success" => false,
				"error_message" => "Les informations du formulaire d'enregistrement ne sont pas correctes"
			]);
		}
		if ($user_repo->findOneBy(["pseudo" => $pseudo])) {
			return new JsonResponse([
				"success" => false,
				"error_message" => "Ce pseudo est déjà utilisé"
			]);
		}
		if ($user_repo->findOneBy(["mail" => $mail])) {
			return new JsonResponse([
				"success" => false,
				"error_message" => "Cette adresse email est déjà utilisé"
			]);
		}

		$user = new User();
		$user->setPseudo($pseudo);
		$user->setMail($mail);
		$user->setPassword($password);
		$user->setPoints(10);
		$user->setValidateAccountKey($api->generateToken());
		$user->setToken($api->generateToken());
		$em->persist($user);
		$password = $password_encoder->encodePassword($user, $user->getPassword());
		$user->setPassword($password);
		$user->setCreatedAt(new \DateTime());
		$em->persist($user);

		$base = new Base();
		$base->setName($pseudo."'s base");
		$base->setPoints(10);
		$base->setElectricity(1000);
		$base->setIron(1000);
		$base->setFuel(1000);
		$base->setWater(1000);
		$base->setUser($user);
		$em->persist($base);

		$building = new Building();
		$building->setArrayName("command_center");
		$building->setName("Command center");
		$building->setLevel(1);
		$building->setLocation(1);
		$building->setBase($base);
		$em->persist($building);

		$em->flush();

		return new JsonResponse([
			"success" => true,
			"error_message" => "Ton compte a été créé. Tu peux maintenant t'y connecter, pense également à le valider avec le amil que tu vas recevoir"
		]);
	}
}