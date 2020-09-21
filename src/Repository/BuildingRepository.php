<?php

namespace App\Repository;

use App\Entity\Base;
use Doctrine\DBAL\Types\Type;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\NonUniqueResultException;

class BuildingRepository extends EntityRepository
{
	/**
	 * get the building in the current base if exist else return null
	 * @param string $array_name
	 * @param Base $base
	 * @return mixed
	 * @throws NonUniqueResultException
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
	 * method that return all ended construction building that are in construction now and must end it
	 * @param Base $base
	 * @return mixed
	 * @throws \Exception
	 */
	public function finByBuildingInConstructionEnded(Base $base)
	{
		$query = $this->getEntityManager()->createQuery("SELECT bu FROM App:Building bu
			JOIN App:Base ba WITH bu.base = ba AND bu.base = :base
			WHERE bu.in_construction = true AND bu.end_construction <= :now
		");
		
		$query->setParameter("base", $base, Type::OBJECT);
		$query->setParameter("now", new \DateTime(), Type::DATETIME);
		
		return $query->getResult();
	}
	
	/**
	 * method that return all ended construction building that are in construction now and must end it
	 * @param Base $base
	 * @return mixed
	 * @throws \Exception
	 */
	public function finByBuildingInConstruction(Base $base)
	{
		$query = $this->getEntityManager()->createQuery("SELECT bu FROM App:Building bu
			JOIN App:Base ba WITH bu.base = ba AND bu.base = :base
			WHERE bu.in_construction = true
			ORDER BY bu.start_construction
		");
		
		$query->setParameter("base", $base, Type::OBJECT);
		
		return $query->getResult();
	}
	
	/**
	 * methods that return array_name of building of the base
	 * @param Base $base
	 * @return array
	 */
	public function finByBuildingArrayNameInBase(Base $base)
	{
		$query = $this->getEntityManager()->createQuery("SELECT bu.array_name, bu.level, bu.in_construction FROM App:Building bu
			JOIN App:Base ba WITH bu.base = ba AND bu.base = :base
			ORDER BY bu.array_name
		");
		
		$query->setParameter("base", $base, Type::OBJECT);
		$results = $query->getArrayResult();
		
		if (count($results) > 0) {
			$return_results = [];
			foreach ($results as $result) {
				$level = $result["level"];

				if ($level > 0 || $result["in_construction"] === true) {
					$return_results[$result["array_name"]] = $level;
				}
			}
			
			return $return_results;
		}
		
		return [];
	}
}