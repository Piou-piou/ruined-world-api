<?php

namespace App\Repository;

use App\Entity\Base;
use Doctrine\DBAL\Types\Type;
use Doctrine\ORM\EntityRepository;

class MarketMovementRepository extends EntityRepository
{
	/**
	 * method that return all ended movements
	 * @param Base $base
	 * @return mixed
	 * @throws \Exception
	 */
	public function findByMovementEnded(Base $base)
	{
		$query = $this->getEntityManager()->createQuery("SELECT mm FROM App:MarketMovement mm
			WHERE mm.base = :base AND mm.end_date < :now
		");

		$query->setParameter("base", $base, Type::OBJECT);
		$query->setParameter("now", new \DateTime(), Type::DATETIME);

		return $query->getResult();
	}
}