<?php

namespace App\Repository;

use App\Entity\Base;
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

	/**
	 * method that find base that have same name as base_name and different of base passed
	 * @param Base $base
	 * @param $base_name
	 * @return int
	 */
	public function findByBaseNameExist(Base $base, string $base_name)
	{
		$query = $this->getEntityManager()->createQuery("SELECT b FROM App:Base b
			WHERE b.archived = false AND b.name = :base_name AND b.id != :base_id
		");
		$query->setParameter("base_name", $base_name);
		$query->setParameter("base_id", $base->getId());

		return $query->getResult();
	}
	
	/**
	 * method that find bases to send to map with only necessary infos
	 * @return mixed
	 */
	public function findByBasesForMap()
	{
		$query = $this->getEntityManager()->createQuery("SELECT b.guid, b.name, b.points, b.posx, b.posy, b.archived, u.pseudo
			FROM App:Base b
			JOIN App:User u WITH b.user = u
		");
		
		return $query->getResult();
	}
}