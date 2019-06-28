<?php

namespace App\Repository;

use App\Entity\Base;
use App\Entity\MarketMovement;
use Doctrine\DBAL\Types\Type;
use Doctrine\ORM\EntityRepository;

class MarketMovementRepository extends EntityRepository
{
	/**
	 * @param Base $base
	 * @return int
	 */
	public function findByTraderInMove(Base $base): int
	{
		$query = $this->getEntityManager()->createQuery("SELECT mm.trader_number FROM App:MarketMovement mm
			WHERE mm.base = :base
		");
		$query->setParameter("base", $base, Type::OBJECT);

		$results = $query->getResult();
		$trader_inmove = 0;

		foreach ($results as $result) {
			$trader_inmove += $result["trader_number"];
		}

		return $trader_inmove;
	}

	/**
	 * get current market movements (that depart or arrive to base in parameter)
	 * @param Base $base
	 * @return mixed
	 * @throws \Exception
	 */
	public function findByCurrentMovements(Base $base)
	{
		$query = $this->getEntityManager()->createQuery("SELECT mm FROM App:MarketMovement mm
			WHERE (mm.base = :base OR (mm.baseDest = :base AND mm.type = :go_movement)) AND mm.end_date >= :now
		");

		$query->setParameter("base", $base, Type::OBJECT);
		$query->setParameter("go_movement", MarketMovement::TYPE_GO, Type::INTEGER);
		$query->setParameter("now", new \DateTime(), Type::DATETIME);

		return $query->getResult();
	}

	/**
	 * method that return all ended movements
	 * @param Base $base
	 * @return mixed
	 * @throws \Exception
	 */
	public function findByMovementEnded(Base $base)
	{
		$query = $this->getEntityManager()->createQuery("SELECT mm FROM App:MarketMovement mm
			WHERE mm.base = :base AND mm.end_date <= :now
		");

		$query->setParameter("base", $base, Type::OBJECT);
		$query->setParameter("now", new \DateTime(), Type::DATETIME);

		return $query->getResult();
	}
}