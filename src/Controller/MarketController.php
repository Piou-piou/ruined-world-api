<?php

namespace App\Controller;

use App\Entity\Base;
use App\Entity\MarketMovement;
use App\Service\Globals;
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
     * @return JsonResponse
     * @throws \Exception
     */
    public function sendResources(Session $session, Globals $globals): JsonResponse
    {
        $em = $this->getDoctrine()->getManager();
        $today = new \DateTime();
        $base = $globals->getCurrentBase();
        $infos = $session->get("jwt_infos");

        $other_base = $em->getRepository(Base::class)->findOneBy(["posx" => $infos->posx, "posy" => $infos->posy, "archived" => false]);

        if (!$other_base) {
            return new JsonResponse([
                "success" => false,
                "error_message" => "Aucune base trouvée aux positions " . $infos->posx . ", " . $infos->posy
            ]);
        }

        $travel_time = $globals->getTimeToTravel($base, $other_base, 1);
        $end_date = $today->add(new \DateInterval("PT".$travel_time."S"));

        $market_movement = new MarketMovement();
        $market_movement->setBase($base);
        $market_movement->setBaseIdDest($other_base->getId());
        $market_movement->setDuration($travel_time);
        $market_movement->setEndDate($end_date);
        $market_movement->setResources($infos->resources);
        $em->persist($market_movement);
        $em->flush();

        return new JsonResponse([
            "success" => true,
        ]);
    }
}