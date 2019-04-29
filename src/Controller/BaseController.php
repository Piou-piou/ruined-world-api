<?php

namespace App\Controller;

use App\Entity\Base;
use App\Service\Api;
use App\Service\Globals;
use App\Service\Resources;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Routing\Annotation\Route;

class BaseController extends AbstractController
{
    /**
     * @Route("/api/main-base/", name="main_base", methods={"POST"})
     * @param Session $session
     * @return JsonResponse
     */
    public function getMainBase(Session $session): JsonResponse
    {
        $main_base = $this->getDoctrine()->getRepository(Base::class)->findOneBy([
            "user" => $session->get("user"),
            "archived" => false
        ], ["id" => "asc"]);
        $guid_base = null;
        $success = false;

        if ($main_base) {
            $guid_base = $main_base->getGuid();
            $success = true;
        }

        return new JsonResponse([
            "success" => $success,
            "token" => $session->get("user")->getToken(),
            "guid_base" => $guid_base
        ]);
    }

    /**
     * @Route("/api/base/", name="base", methods={"POST"})
     * @param Session $session
     * @param Globals $globals
     * @param Api $api
     * @param Resources $resources
     * @return JsonResponse
     */
    public function sendInfos(Session $session, Globals $globals, Api $api, Resources $resources): JsonResponse
    {
        $base = $globals->getCurrentBase();

        return new JsonResponse([
            "success" => true,
            "token" => $session->get("user")->getToken(),
            "base" => $api->serializeObject($base),
            "resources_infos" => [
                "max_storage" => $resources->getWarehouseCapacity(),
                "electricity_production" => $resources->getElectricityProduction(),
                "iron_production" => $resources->getIronProduction(),
                "fuel_production" => $resources->getFuelProduction(),
                "water_production" => $resources->getWaterProduction(),
            ]
        ]);
    }

    /**
     * @Route("/api/refresh-resources/", name="refresh_resources", methods={"POST"})
     * @param Session $session
     * @param Globals $globals
     * @return JsonResponse
     */
    public function sendResources(Session $session, Globals $globals): JsonResponse
    {
        $base = $globals->getCurrentBase();

        return new JsonResponse([
            "token" => $session->get("user")->getToken(),
            "electricity" => $base->getElectricity(),
            "iron" => $base->getIron(),
            "fuel" => $base->getFuel(),
            "water" => $base->getWater()
        ]);
    }
}