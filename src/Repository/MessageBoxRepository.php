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
			WHERE mb.user = :user AND mb.archived_sent = false AND mb.archived = false AND mb.read_at IS NULL
			AND mb.type != :send
		");
		$query->setParameter("user", $user, Type::OBJECT);
		$query->setParameter("send", MessageBox::TYPE_SEND, Type::INTEGER);

		return count($query->getResult());
	}
}