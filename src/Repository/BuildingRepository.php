<?php

namespace App\Repository;

use App\Entity\Base;
use Doctrine\DBAL\Types\Type;
use Doctrine\ORM\EntityRepository;

class BuildingRepository extends EntityRepository
{
	/**
	 * get the building in the current base if exist else return null
	 * @param string $array_name
	 * @param Base $base
	 * @return mixed
	 * @throws \Doctrine\ORM\NonUniqueResultException
	 */
	public function findByBuildingInBase(string $array_name, Base $base)
	{
		$query = $this->getEntityManager()->createQuery("SELECT bu FROM App:Building bu
			JOIN App:Base ba WITH bu.base = ba AND bu.base = :base
			WHERE bu.array_name = :array_name
		");
		
		$query->setParameter("base", $base, Type::OBJECT);
		$query->setParameter("array_name", $array_name, Type::STRING);
		
		return $query->getOneOrNullResult();
	}
	
	/**
	 * method that return all ended construciton building that are in construction now and must end it
	 * @param Base $base
	 * @return mixed
	 * @throws \Exception
	 */
	public function finByBuildingInConstructionEnded(Base $base)
	{
		$query = $this->getEntityManager()->createQuery("SELECT bu FROM App:Building bu
			JOIN App:Base ba WITH bu.base = ba AND bu.base = :base
			WHERE bu.in_construction = true AND bu.end_construction < :now
		");
		
		$query->setParameter("base", $base, Type::OBJECT);
		$query->setParameter("now", new \DateTime(), Type::DATETIME);
		
		return $query->getResult();
	}
}