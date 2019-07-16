<?php


namespace App\Repository;


use App\Entity\Base;
use Doctrine\DBAL\Types\Type;
use Doctrine\ORM\EntityRepository;

class MissionRepository extends EntityRepository
{
	/**
	 * method to find available mission for a base
	 * @param Base $base
	 * @return mixed
	 */
	public function findByMissionAvailable(Base $base)
	{
		$query = $this->getEntityManager()->createQuery("SELECT m FROM App:Mission m
			WHERE m.base = :base AND m.in_progress != true AND m.disabled != true
		");
		$query->setParameter("base", $base, Type::OBJECT);

		return $query->getResult();
	}
}