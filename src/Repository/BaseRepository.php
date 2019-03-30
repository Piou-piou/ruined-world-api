<?php

namespace App\Repository;

use Doctrine\ORM\EntityRepository;

class BaseRepository extends EntityRepository
{
	/**
	 * method that send base of users that aren't in holidays mode
	 * @return mixed
	 * @throws \Exception
	 */
	public function findByBaseUserNotHolidays()
	{
		$query = $this->getEntityManager()->createQuery("SELECT b FROM App:Base b
			JOIN App:User u WITH b.user = u
			WHERE u.holidays = false
		");
		
		return $query->getResult();
	}
}