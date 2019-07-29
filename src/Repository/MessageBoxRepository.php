<?php

namespace App\Repository;

use App\Entity\User;
use Doctrine\DBAL\Types\Type;
use Doctrine\ORM\EntityRepository;

class MessageBoxRepository extends EntityRepository
{
	/**
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
}