<?php

namespace App\Repository;

use Doctrine\DBAL\Types\Type;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Exception;

class UserRepository extends EntityRepository
{
	/**
	 * method to count number of players that aren't archived
	 * @return mixed
	 * @throws NonUniqueResultException
	 */
	public function findByCountRankedPlayers()
	{
		$query = $this->getEntityManager()->createQuery("SELECT count(u) FROM App:User u WHERE
			u.archived = false
			AND u.pseudo != 'world-center'
		");

		return $query->getOneOrNullResult()[1];
	}

	/**
	 * method to get users that hasn't connected to the game for a certain time and that are not archived yet
	 * @param int $max_inactivation_days
	 * @return mixed
	 * @throws Exception
	 */
	public function findByUserToArchive(int $max_inactivation_days)
	{
		$now = new \DateTime();
		$now->sub(new \DateInterval("P" . $max_inactivation_days . "D"));
		
		$query = $this->getEntityManager()->createQuery("SELECT u FROM App:User u WHERE
			u.last_connection < :max_inactivation_days 
			AND u.holidays = false
			AND u.archived = false
			AND u.pseudo != 'world-center'
		");
		
		$query->setParameter("max_inactivation_days", $now, Type::DATETIME);
		
		return $query->getResult();
	}
	
	/**
	 * @param int $max_holidays_days
	 * @return mixed
	 * @throws Exception
	 */
	public function findByUserEndHolidays(int $max_holidays_days)
	{
		$now = new \DateTime();
		$now->sub(new \DateInterval("P" . $max_holidays_days . "D"));
		
		$query = $this->getEntityManager()->createQuery("SELECT u FROM App:User u WHERE
			u.last_connection < :max_holidays_days
			AND u.archived = false
			AND u.holidays = true
			AND u.pseudo != 'world-center'
		");
		
		$query->setParameter("max_holidays_days", $now, Type::DATETIME);
		
		return $query->getResult();
	}

	/**
	 * method to get user for rankin page
	 * @param $limit
	 * @param $offset
	 * @return int|mixed|string
	 */
	public function findByRank($limit, $offset)
	{
		$query = $this->getEntityManager()->createQuery("SELECT u FROM App:User u WHERE
			u.archived = false
			AND u.pseudo != 'world-center'
			ORDER BY u.points DESC
		");

		$query->setMaxResults($limit);
		$query->setFirstResult($offset);

		return $query->getResult();
	}
}