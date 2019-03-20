<?php

namespace App\Repository;

use Doctrine\ORM\EntityRepository;

class UserRepository extends EntityRepository
{
	/**
	 * method to get users that hasn't connected to the game for a certain time and that are not archived yet
	 * @param int $max_inactivation_days
	 * @return mixed
	 * @throws \Exception
	 */
	public function findByUserToArchive(int $max_inactivation_days)
	{
		$now = new \DateTime();
		$now->sub(new \DateInterval("P".$max_inactivation_days."D"));
		
		$query = $this->getEntityManager()->createQuery("SELECT u FROM App:User u WHERE
			u.last_connection < :max_inactivation_days AND
			u.archived = false
		");
		
		$query->setParameter("max_inactivation_days", $now);
		
		return $query->getResult();
	}
}