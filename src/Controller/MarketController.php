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
        $other_base = $em->getRepository(Base::class)->findOneBy(["posx" => $infos->posx, "posy" => $infos->posy, "archived" => false]);
		$resources_to_send = (array)$infos->resources;

        if (!$other_base) {
            return new JsonResponse([
                "success" => false,
                "error_message" => "Aucune base trouvée aux positions " . $infos->posx . ", " . $infos->posy
            ]);
        }
        
        $enough_traders = $market->testIfEnoughTrader($resources_to_send);
        if (!$enough_traders) {
			return new JsonResponse([
				"success" => false,
				"error_message" => "Vous n'avez pas assez de marchand disponible dans votre base pour ce transport"
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
        ]);
    }

	/**
	 * method that send current market movement of current base
	 * @Route("/api/market/send-current-movements/", name="merket_send_movements", methods={"POST"})
	 * @param Globals $globals
	 * @return JsonResponse
	 */
    public function sendMarketMovements(Globals $globals): JsonResponse
	{
		$movements = [];
		$market_movements = $this->getDoctrine()->getRepository(MarketMovement::class)->findBy([
			"base" => $globals->getCurrentBase()
		]);

		/** @var MarketMovement $market_movement */
		foreach ($market_movements as $market_movement) {
			$movements[] = [
				"type" => $market_movement->getType(),
				"endTransport" => $market_movement->getEndDate()->getTimestamp(),
				"base_dest_name" => $market_movement->getBaseDest()->getName()
			];
		}

		return new JsonResponse([
			"success" => true,
			"market_movements" => $movements
		]);
	}
}