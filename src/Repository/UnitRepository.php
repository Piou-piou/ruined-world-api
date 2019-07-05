<?php

namespace App\Repository;

use App\Entity\Base;
use Doctrine\DBAL\Types\Type;
use Doctrine\ORM\EntityRepository;

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
}