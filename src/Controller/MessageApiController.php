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
	 * @param Api $api
	 * @return JsonResponse
	 */
	public function showMessagesOfBox(Session $session, Api $api): JsonResponse
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

	/**
	 * method to send a message
	 * @Route("/api/message/show/", name="message_show", methods={"POST"})
	 * @param Session $session
	 * @param Api $api
	 * @return JsonResponse
	 */
	public function showMessage(Session $session, Api $api): JsonResponse
	{
		$infos = $session->get("jwt_infos");
		$message = $this->getDoctrine()->getManager()->getRepository(MessageBox::class)->find($infos->message_id);

		return new JsonResponse([
			"success" => true,
			"token" => $session->get("user")->getToken(),
			"message" => $api->serializeObject($message)
		]);
	}

	/**
	 * method to delete a message of the box
	 * @Route("/api/message/delete/", name="message_delete", methods={"POST"})
	 * @param Session $session
	 * @return JsonResponse
	 */
	public function deleteMessage(Session $session): JsonResponse
	{
		$em = $this->getDoctrine()->getManager();
		$infos = $session->get("jwt_infos");
		$message = $this->getDoctrine()->getManager()->getRepository(MessageBox::class)->find($infos->message_id);
		$error_message = "";
		$success_message = "";

		if ($message) {
			$em->remove($message);
			$em->flush();
			$success_message = "Le message a été supprimé";
		} else {
			$error_message = "Le message demandé n'existe pas ou plus";
		}

		return new JsonResponse([
			"success" => true,
			"token" => $session->get("user")->getToken(),
			"success_message" => $success_message,
			"error_message" => $error_message
		]);
	}

	/**
	 * method to delete some messages of the box
	 * @Route("/api/messages/delete/", name="messages_delete", methods={"POST"})
	 * @param Session $session
	 * @return JsonResponse
	 */
	public function deleteMessages(Session $session): JsonResponse
	{
		$em = $this->getDoctrine()->getManager();
		$infos = $session->get("jwt_infos");
		
		foreach ($infos->messages as $id_message) {
			$message = $em->getRepository(MessageBox::class)->find($id_message);
			if ($message) {
				$em->remove($message);
				$em->flush();
			}
		}

		return new JsonResponse([
			"success" => true,
			"token" => $session->get("user")->getToken(),
			"success_message" => "Les messages ont été supprimés",
		]);
	}
}