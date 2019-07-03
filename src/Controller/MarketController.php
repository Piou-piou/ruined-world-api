<?php

namespace App\Controller;

use App\Entity\Base;
use App\Entity\MarketMovement;
use App\Service\Globals;
use App\Service\Market;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Routing\Annotation\Route;

class MarketController extends AbstractController
{
	/**
	 * method to send some resources to an other base
	 * @Route("/api/market/send-resources/", name="merket_send_resource", methods={"POST"})
	 * @param Session $session
	 * @param Globals $globals
	 * @param Market $market
	 * @return JsonResponse
	 * @throws \Exception
	 */
    public function sendResources(Session $session, Globals $globals, Market $market): JsonResponse
    {
        $em = $this->getDoctrine()->getManager();
        $today = new \DateTime();
        $base = $globals->getCurrentBase();
        $infos = $session->get("jwt_infos");
        $posx = $infos->posx ? $infos->posx : 0;
        $posy = $infos->posy ? $infos->posy : 0;
        $other_base = $em->getRepository(Base::class)->findOneBy(["posx" => $posx, "posy" => $posy, "archived" => false]);
		$resources_to_send = (array)$infos->resources;

        if (!$other_base) {
            return new JsonResponse([
                "success" => false,
                "error_message" => "Aucune base trouvée aux positions " . $posx . ", " . $posy,
				"token" => $session->get("user")->getToken(),
            ]);
        }
        
        $enough_traders = $market->testIfEnoughTrader($resources_to_send);
        if (!$enough_traders) {
			return new JsonResponse([
				"success" => false,
				"error_message" => "Vous n'avez pas assez de marchand disponible dans votre base pour ce transport",
				"token" => $session->get("user")->getToken(),
			]);
		}

        $travel_time = $globals->getTimeToTravel($base, $other_base, 1);
        $end_date = $today->add(new \DateInterval("PT".$travel_time."S"));

        $market_movement = new MarketMovement();
        $market_movement->setBase($base);
        $market_movement->setBaseDest($other_base);
        $market_movement->setDuration($travel_time);
        $market_movement->setEndDate($end_date);
        $market_movement->setResources($resources_to_send);
        $market_movement->setTraderNumber($market->getTraderToTransport($resources_to_send));
        $market_movement->setType(MarketMovement::TYPE_GO);
        $em->persist($market_movement);
        $em->flush();

        return new JsonResponse([
            "success" => true,
			"success_message" => "Vos marchands, se mettent en route immédiatement !",
			"token" => $session->get("user")->getToken(),
        ]);
    }

	/**
	 * method that send current market movement of current base
	 * @Route("/api/market/send-current-movements/", name="market_send_movements", methods={"POST"})
	 * @param Globals $globals
	 * @param Session $session
	 * @return JsonResponse
	 * @throws \Exception
	 */
    public function sendMarketMovements(Globals $globals, Session $session): JsonResponse
	{
		$movements = [];
		$market_movements = $this->getDoctrine()->getRepository(MarketMovement::class)->findByCurrentMovements($globals->getCurrentBase());

		/** @var MarketMovement $market_movement */
		foreach ($market_movements as $market_movement) {
			$movements[] = [
				"type" => $market_movement->getType(),
				"endTransport" => $market_movement->getEndDate()->getTimestamp(),
				"base_dest_name" => $market_movement->getBaseDest()->getName(),
				"base_dest_guid" => $market_movement->getBaseDest()->getGuid()
			];
		}

		return new JsonResponse([
			"success" => true,
			"market_movements" => $movements,
			"token" => $session->get("user")->getToken(),
		]);
	}

	/**
	 * method that update current movements of trader in base
	 * @Route("/api/market/update-current-movements/", name="market_update_movements", methods={"POST"})
	 * @param Globals $globals
	 * @param Market $market
	 * @return JsonResponse
	 * @throws \Exception
	 */
	public function updateMarketMovements(Globals $globals, Market $market): JsonResponse
	{
		$market->updateMarketMovement($globals->getCurrentBase(true));

		return $this->sendMarketMovements($globals);
	}

	/**
	 * method to send current market number in base
	 * @Route("/api/market/send-current-market-number/", name="market_send_trader_number", methods={"POST"})
	 * @param Market $market
	 * @param Session $session
	 * @return JsonResponse
	 */
	public function sendCurrentTradersInBase(Market $market, Session $session): JsonResponse
	{
		return new JsonResponse([
			"success" => true,
			"trader_number" => $market->getTraderNumberInBase(),
			"max_trader_number" => $market->getMaxtraderInBase(),
			"token" => $session->get("user")->getToken(),
		]);
	}
}