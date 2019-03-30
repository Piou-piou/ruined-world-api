<?php

namespace App\Controller;

use App\Entity\Building;
use App\Service\Globals;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;

class BuildingController extends AbstractController
{
	/**
	 * method to build or upgrade a building of a base
	 * @Route("/buildings/build/", name="build_building", methods={"POST"})
	 * @param SessionInterface $session
	 * @param Globals $globals
	 * @param \App\Service\Building $building_service
	 * @return JsonResponse
	 * @throws \Doctrine\ORM\NonUniqueResultException
	 * @throws \Exception
	 */
	public function buildOrUpgrade(SessionInterface $session, Globals $globals, \App\Service\Building $building_service): JsonResponse
	{
		$em = $this->getDoctrine()->getManager();
		$infos = $session->get("jwt_infos");
		$now = new \DateTime();
		$building_config = $globals->getBuildingsConfig()[$infos->array_name];
		$base = $globals->getCurrentBase();
		
		/**
		 * @var $building Building
		 */
		$building = $em->getRepository(Building::class)->findByBuildingInBase($infos->array_name, $base);
		
		if (!$building) {
			$building = new Building();
			$building->setName($building_config["name"]);
			$building->setArrayName($infos->array_name);
			$building->setLocation($infos->case);
			$building->setBase($base);
		}
		
		if ($building->getInConstruction() === true) {
			return new JsonResponse([
				"success" => false,
				"message" => "A building is already in construction in your base.",
				"token" => $session->get("user")->getToken(),
			]);
		}
		
		$building->setInConstruction(true);
		$end_construction = $now->add(new \DateInterval("PT" . $building_service->getConstructionTime($infos->array_name, $building->getLevel() + 1) . "S"));
		$building->setEndConstruction($end_construction);
		
		if ($building_service->testWithdrawResourcesToBuild($infos->array_name) === false) {
			return new JsonResponse([
				"success" => false,
				"message" => "You haven't enough resources",
				"token" => $session->get("user")->getToken(),
			]);
		}
		
		$em->persist($building);
		$em->flush();
		
		return new JsonResponse([
			"success" => true,
			"token" => $session->get("user")->getToken(),
		]);
	}
}