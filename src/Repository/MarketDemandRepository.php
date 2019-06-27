<?php

namespace App\Repository;

use App\Entity\Base;
use Doctrine\DBAL\Types\Type;
use Doctrine\ORM\EntityRepository;

class MarketDemandRepository extends EntityRepository
{
	/**
	 * @param Base $base
	 * @return int
	 */
	public function findByTraderReservedOnDemands(Base $base): int
	{
		$query = $this->getEntityManager()->createQuery("SELECT md.trader_number_asked FROM App:MarketDemand md
			WHERE md.base = :base
		");
		$query->setParameter("base", $base, Type::OBJECT);

		$results = $query->getResult();
		$trader_indemand = 0;

		foreach ($results as $result) {
			$trader_indemand += $result["trader_number_asked"];
		}

		return $trader_indemand;
	}
}