<?php

namespace App\Controller;

use App\Entity\Building;
use App\Entity\League;
use App\Service\Api;
use App\Service\Globals;
use Doctrine\Common\Annotations\AnnotationException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Exception\ExceptionInterface;

class EmbassyController extends AbstractController
{
	/**
	 * @Route("/api/embassy/show/", name="embassy_show", methods={"POST"})
	 * @param SessionInterface $session
	 * @param Api $api
	 * @param Globals $globals
	 * @return JsonResponse
	 * @throws AnnotationException
	 * @throws ExceptionInterface
	 */
	public function sendMyEmbassy(SessionInterface $session, Api $api, Globals $globals): JsonResponse
	{
		$em = $this->getDoctrine()->getManager();

		$league = $em->getRepository(League::class)->findOneBy(["leader" => $session->get("user")]);
		$embassy = $em->getRepository(Building::class)->findOneBy([
			"array_name" => "embassy",
			"base" => $globals->getCurrentBase()
		]);

		return new JsonResponse([
			"success" => true,
			"token" => $session->get("user_token")->getToken(),
			"league" => $api->serializeObject($league),
			"embassy" => $api->serializeObject($embassy)
		]);
	}
}