<?php

namespace App\Repository;

use Doctrine\DBAL\Types\Type;
use Doctrine\ORM\EntityRepository;
use Exception;

class UserTokenRepository extends EntityRepository
{
	/**
	 * method to find UserToken to archive
	 * @param int $max_inactivation_days
	 * @return mixed
	 * @throws Exception
	 */
	public function findByExpiredToken(int $max_inactivation_days)
	{
		$now = new \DateTime();
		$now->sub(new \DateInterval("P" . $max_inactivation_days . "D"));

		$query = $this->getEntityManager()->createQuery("SELECT u FROM App:UserToken u WHERE
			u.endToken < :max_inactivation_days
		");

		$query->setParameter("max_inactivation_days", $now, Type::DATETIME);

		return $query->getResult();
	}
}