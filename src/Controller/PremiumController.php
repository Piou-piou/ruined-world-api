<?php

namespace App\Controller;

use App\Entity\User;
use App\Service\Globals;
use DateInterval;
use DateTime;
use Exception;
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
			"premium_config" => $premium,
			"enabled_advantages" => $session->get("user")->getPremiumAdvantages()
		]);
	}

	/**
	 * return method name for current premium config
	 * @param string $array_name
	 * @return string
	 */
	private function getMethodNamePremiumConfig(string $array_name): string
	{
		$pos_underscore = strpos($array_name, "_");
		$underscore_letter = substr($array_name, $pos_underscore, 2);
		$letter = explode("_", $underscore_letter)[1];

		return "setPremium".ucfirst(str_replace($underscore_letter, strtoupper($letter), $array_name));
	}

	/**
	 * method that enable advantage for user if he has enough money
	 * @Route("/api/premium/buy-advantage/", name="premium_buy_advantage", methods={"POST"})
	 * @param SessionInterface $session
	 * @param Globals $globals
	 * @return JsonResponse
	 * @throws Exception
	 */
	public function enableAdvantage(SessionInterface $session, Globals $globals): JsonResponse
	{
		$em = $this->getDoctrine()->getManager();
		/** @var User $user */
		$user = $session->get("user");
		$infos = $session->get("jwt_infos");
		$premium_config = $globals->getPremiumConfig()[$infos->array_name];
		$cost = $premium_config[$infos->cost];

		if ($user->getPremiumMoney() - $cost <= 0) {
			return new JsonResponse([
				"success" => false,
				"error_message" => "Vous n'avez pas assez de tougnous pour acheter cet avantage",
				"token" => $session->get("user_token")->getToken(),
			]);
		}

		$method_premium = $this->getMethodNamePremiumConfig($premium_config["array_name"]);
		$user->$method_premium((new DateTime())->add(new DateInterval("P".explode("cost", $infos->cost)[1]."D")));
		$user->setPremiumMoney($user->getPremiumMoney() - $cost);
		$em->persist($user);
		$em->flush();

		return new JsonResponse([
			"success" => true,
			"success_message" => "Merci pour votre achat de l'avantage : ". $premium_config["title"] .". Il sera activé dans les secondes à venir",
			"token" => $session->get("user_token")->getToken(),
		]);
	}
}