<?php

namespace App\Controller;

use App\Entity\Message;
use App\Entity\MessageBox;
use App\Entity\User;
use App\Service\Api;
use DateTime;
use Doctrine\Common\Annotations\AnnotationException;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Exception\ExceptionInterface;

class MessageController extends AbstractController
{
	/**
	 * method to get message received in box based on a given type
	 * @Route("/api/message/list/", name="message_list", methods={"POST"})
	 * @param Session $session
	 * @param Api $api
	 * @return JsonResponse
	 * @throws AnnotationException
	 * @throws ExceptionInterface
	 */
	public function showMessagesOfBox(Session $session, Api $api): JsonResponse
	{
		$infos = $session->get("jwt_infos");
		$user = $session->get("user");
		$message_box = $this->getDoctrine()->getManager()->getRepository(MessageBox::class);

		if (isset($infos->type)) {
			if ($infos->type === "send") {
				$messages = $message_box->findBySentMessageBox($user);
			} else {
				$types = [
					"received" => MessageBox::TYPE_RECEIVED,
					"fight-report" => MessageBox::FIGHT_REPORT,
					"other-report" => MessageBox::TYPE_OTHER,
				];

				$messages = $message_box->findBy([
					"user" => $user,
					"type" => $types[$infos->type],
					"archived" => false
				], ["id" => "DESC"]);
			}
		}

		return new JsonResponse([
			"success" => true,
			"token" => $session->get("user_token")->getToken(),
			"messages" => $api->serializeObject($messages)
		]);
	}

	/**
	 * method to send number of unread messages
	 * @Route("/api/message/unread-number/", name="message_unread_number", methods={"POST"})
	 * @param Session $session
	 * @return JsonResponse
	 */
	public function sendUnreadMessagesNumber(Session $session): JsonResponse
	{
		$user = $session->get("user");
		$message_box = $this->getDoctrine()->getManager()->getRepository(MessageBox::class)->findByNumberUnreadMessages($user);

		return new JsonResponse([
			"success" => true,
			"token" => $session->get("user_token")->getToken(),
			"nb_unread" => $message_box
		]);
	}

	/**
	 * method to send number of unread messages grouped by box
	 * @Route("/api/message/unread-number-box/", name="message_unread_number_box", methods={"POST"})
	 * @param Session $session
	 * @return JsonResponse
	 */
	public function sendUnreadMessagesNumberPerBox(Session $session): JsonResponse
	{
		$user = $session->get("user");
		$unread_messages = $this->getDoctrine()->getManager()->getRepository(MessageBox::class)->findByNumberUnreadMessagesPerBox($user);
		$unread = 0;
		$unread_fight_report = 0;
		$unread_other_report = 0;

		foreach ($unread_messages as $unread_message) {
			if ($unread_message["type"] === MessageBox::TYPE_RECEIVED) {
				$unread = $unread_message["nb_unread"];
			} else if ($unread_message["type"] === MessageBox::FIGHT_REPORT) {
				$unread_fight_report = $unread_message["nb_unread"];
			} else if ($unread_message["type"] === MessageBox::TYPE_OTHER) {
				$unread_other_report = $unread_message["nb_unread"];
			}
		}

		return new JsonResponse([
			"success" => true,
			"token" => $session->get("user_token")->getToken(),
			"unread_messages" => $unread,
			"unread_fight_report" => $unread_fight_report,
			"unread_other_report" => $unread_other_report,
		]);
	}

	/**
	 * method to send a message
	 * @Route("/api/message/show/", name="message_show", methods={"POST"})
	 * @param Session $session
	 * @param Api $api
	 * @return JsonResponse
	 * @throws Exception
	 * @throws ExceptionInterface
	 */
	public function showMessage(Session $session, Api $api): JsonResponse
	{
		$em = $this->getDoctrine()->getManager();
		$infos = $session->get("jwt_infos");
		$message = $em->getRepository(MessageBox::class)->find($infos->message_id);

		if ($message) {
			$message->setReadAt(new DateTime());
			$em->persist($message);
			$em->flush();
		}

		return new JsonResponse([
			"success" => true,
			"token" => $session->get("user_token")->getToken(),
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
		$message = $em->getRepository(MessageBox::class)->find($infos->message_id);
		$error_message = "";
		$success_message = "";

		if ($message) {
			$message->setArchived(true);
			$em->persist($message);
			$em->flush();
			$success_message = "Le message a été supprimé";
		} else {
			$error_message = "Le message demandé n'existe pas ou plus";
		}

		return new JsonResponse([
			"success" => true,
			"token" => $session->get("user_token")->getToken(),
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
				if ($infos->type !== "send") {
					$message->setArchived(true);
				} else {
					$message->setArchivedSent(true);
				}
				$em->persist($message);
				$em->flush();
			}
		}

		return new JsonResponse([
			"success" => true,
			"token" => $session->get("user_token")->getToken(),
			"success_message" => "Les messages ont été supprimés"
		]);
	}

	/**
	 * method to set some messages of the box as read
	 * @Route("/api/messages/read/", name="messages_read", methods={"POST"})
	 * @param Session $session
	 * @return JsonResponse
	 * @throws Exception
	 */
	public function readMessages(Session $session): JsonResponse
	{
		$em = $this->getDoctrine()->getManager();
		$infos = $session->get("jwt_infos");

		foreach ($infos->messages as $id_message) {
			$message = $em->getRepository(MessageBox::class)->find($id_message);
			if ($message && $session->get("user") !== $message->getMessage()->getUser()) {
				$message->setReadAt(new DateTime());
				$em->persist($message);
				$em->flush();
			}
		}

		return new JsonResponse([
			"success" => true,
			"token" => $session->get("user_token")->getToken(),
			"success_message" => "Les messages ont été marqué comme lu"
		]);
	}

	/**
	 * @param $infos
	 * @return User|null
	 */
	private function getUserForMessage($infos)
	{
		$em = $this->getDoctrine()->getManager();
		$user = null;

		if (isset($infos->user_id) && $infos->user_id !== null) {
			$user = $em->getRepository(User::class)->findOneBy([
				"id" => $infos->user_id,
				"archived" => false
			]);
		} else if (isset($infos->pseudo) && $infos->pseudo !== null) {
			$user = $em->getRepository(User::class)->findOneBy([
				"pseudo" => $infos->pseudo,
				"archived" => false
			]);
		}

		return $user;
	}

	/**
	 * method to send a message to a player
	 * @Route("/api/message/send/", name="message_send", methods={"POST"})
	 * @param Session $session
	 * @return JsonResponse
	 * @throws Exception
	 */
	public function sendMessage(Session $session): JsonResponse
	{
		$em = $this->getDoctrine()->getManager();
		$infos = $session->get("jwt_infos");
		$dest_user = $this->getUserForMessage($infos);

		if (!$dest_user) {
			return new JsonResponse([
				"success" => false,
				"token" => $session->get("user_token")->getToken(),
				"error_message" => "Le joueur " . $infos->pseudo . " n'a pas été trouvé"
			]);
		}

		if ($infos->subject && $dest_user) {
			$message = new Message();
			$message->setSubject($infos->subject);
			$message->setMessage($infos->message);
			$message->setSendAt(new DateTime());
			$message->setUser($session->get("user"));
			$em->persist($message);

			$message_box = new MessageBox();
			$message_box->setUser($dest_user);
			$message_box->setMessage($message);
			$message_box->setType(MessageBox::TYPE_RECEIVED);
			$em->persist($message_box);

			$em->flush();
		}

		return new JsonResponse([
			"success" => true,
			"token" => $session->get("user_token")->getToken(),
			"success_message" => "Message envoyé"
		]);
	}
}