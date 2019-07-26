<?php

namespace App\Controller;

use App\Entity\MessageBox;
use App\Service\Api;
use App\Service\Globals;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Routing\Annotation\Route;

class MessageApiController extends AbstractController
{
	/**
	 * @Route("/api/message/list/", name="message_list", methods={"POST"})
	 * @param Session $session
	 * @param Globals $globals
	 * @param Api $api
	 * @return JsonResponse
	 */
	public function getMessagesOfBox(Session $session, Globals $globals, Api $api): JsonResponse
	{
		$user = $session->get("user");

		$messages_box = $this->getDoctrine()->getManager()->getRepository(MessageBox::class)->findBy([
			"user" => $user,
			"type" => MessageBox::TYPE_RECEIVED
		]);

		return new JsonResponse([
			"success" => true,
			"token" => $session->get("user")->getToken(),
			"messages" => $api->serializeObject($messages_box)
		]);
	}
}