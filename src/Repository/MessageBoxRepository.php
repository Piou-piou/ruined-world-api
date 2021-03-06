<?php

namespace App\Repository;

use App\Entity\MessageBox;
use App\Entity\User;
use Doctrine\DBAL\Types\Type;
use Doctrine\ORM\EntityRepository;

class MessageBoxRepository extends EntityRepository
{
	/**
	 * method to get sent messages
	 * @param User $user
	 * @return mixed
	 */
	public function findBySentMessageBox(User $user)
	{
		$query = $this->getEntityManager()->createQuery("SELECT mb FROM App:MessageBox mb
			JOIN App:Message m WITH m = mb.message AND m.user = :user AND mb.archived_sent = false
		");
		$query->setParameter("user", $user, Type::OBJECT);

		return $query->getResult();
	}

	/**
	 * method to get number of unread messages
	 * @param User $user
	 * @return int
	 */
	public function findByNumberUnreadMessages(User $user): int
	{
		$query = $this->getEntityManager()->createQuery("SELECT mb.id FROM App:MessageBox mb
			WHERE mb.user = :user AND mb.archived = false AND mb.read_at IS NULL
			AND mb.type != :send
		");
		$query->setParameter("user", $user, Type::OBJECT);
		$query->setParameter("send", MessageBox::TYPE_SEND, Type::INTEGER);

		return count($query->getResult());
	}

	/**
	 * method to send number of unread messages per box
	 * @param User $user
	 * @return mixed
	 */
	public function findByNumberUnreadMessagesPerBox(User $user)
	{
		$query = $this->getEntityManager()->createQuery("SELECT count(mb.id) as nb_unread, mb.type FROM App:MessageBox mb
			WHERE mb.user = :user AND mb.archived = false AND mb.read_at IS NULL
			AND mb.type != :send
			GROUP BY mb.type
		");
		$query->setParameter("user", $user, Type::OBJECT);
		$query->setParameter("send", MessageBox::TYPE_SEND, Type::INTEGER);

		return $query->getResult();
	}

	/**
	 * method that get all messages to archive
	 * @param $date_to_archive
	 * @return mixed
	 */
	public function findByMessagesToArchive($date_to_archive)
	{
		$query = $this->getEntityManager()->createQuery("SELECT mb FROM App:MessageBox mb
			JOIN App:Message m WITH m = mb.message AND m.send_at < :date_to_archive
			WHERE mb.archived_sent != true OR mb.archived != true
		");
		$query->setParameter("date_to_archive", $date_to_archive, Type::DATETIME);

		return $query->getResult();
	}

	/**
	 * method that get all archived message received and sent
	 * @param $date_to_delete
	 * @return mixed
	 */
	public function findByArchivedMessages($date_to_delete)
	{
		$query = $this->getEntityManager()->createQuery("SELECT mb FROM App:MessageBox mb
			JOIN App:Message m WITH m = mb.message AND m.send_at < :date_to_delete 
			WHERE mb.archived_sent = true AND mb.archived = true
		");
		$query->setParameter("date_to_delete", $date_to_delete, Type::DATETIME);

		return $query->getResult();
	}
}