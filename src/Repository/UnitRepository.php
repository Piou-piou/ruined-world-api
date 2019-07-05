<?php

namespace App\Repository;

use App\Entity\Base;
use Doctrine\DBAL\Types\Type;
use Doctrine\ORM\EntityRepository;
use Exception;

class UnitRepository extends EntityRepository
{

	/**
	 * method to find units that are currently in recruitment in base
	 * @param Base $base
	 * @return array
	 */
	public function findByUnitsInRecruitment(Base $base): array
	{
		$query = $this->getEntityManager()->createQuery("SELECT u.id, u.name, u.array_name, u.end_recruitment, count(u) as number FROM App:Unit u
			WHERE u.base = :base AND u.in_recruitment = true
			GROUP BY u.array_name
		");
		$query->setParameter("base", $base, Type::OBJECT);

		$results = $query->getResult();
		$return_results = [];

		foreach ($results as $result) {
			$return_results[] = [
				"id" => $result["id"],
				"name" => $result["name"],
				"array_name" => $result["array_name"],
				"end_recruitment" => $result["end_recruitment"]->getTimestamp(),
				"number" => $result["number"]
			];
		}

		return $return_results;
	}

	/**
	 * method that return all ended recruitment units that are in recruitment now and must end it
	 * @param Base $base
	 * @return mixed
	 * @throws Exception
	 */
	public function findByRecruitmentEnded(Base $base)
	{
		$query = $this->getEntityManager()->createQuery("SELECT u FROM App:Unit u
			JOIN App:Base ba WITH u.base = ba AND u.base = :base
			WHERE u.in_recruitment = true AND u.end_recruitment <= :now
		");

		$query->setParameter("base", $base, Type::OBJECT);
		$query->setParameter("now", new \DateTime(), Type::DATETIME);

		return $query->getResult();
	}
}