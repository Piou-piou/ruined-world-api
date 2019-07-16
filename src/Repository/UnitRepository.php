<?php

namespace App\Repository;

use App\Entity\Base;
use App\Entity\UnitMovement;
use Doctrine\DBAL\DBALException;
use Doctrine\DBAL\Types\Type;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Exception;

class UnitRepository extends EntityRepository
{
	/**
	 * method to find units that are currently in base
	 * @param Base $base
	 * @return mixed
	 */
	public function findByUnitsInBase(Base $base) {
		$query = $this->getEntityManager()->createQuery("SELECT u.name, u.array_name, count(u) as number FROM App:Unit u
			WHERE u.base = :base AND u.in_recruitment = false AND u.unitMovement IS NULL 
			GROUP BY u.array_name, u.end_recruitment
		");
		$query->setParameter("base", $base, Type::OBJECT);

		return $query->getResult();
	}

	/**
	 * method to count units that are currently in base
	 * @param Base $base
	 * @return mixed
	 * @throws NonUniqueResultException
	 */
	public function countUnitsInBase(Base $base) {
		$query = $this->getEntityManager()->createQuery("SELECT count(u) as number FROM App:Unit u
			WHERE u.base = :base AND u.in_recruitment = false AND u.unitMovement IS NULL 
		");
		$query->setParameter("base", $base, Type::OBJECT);

		return $query->getOneOrNullResult()["number"];
	}

	/**
	 * method to find units that are currently in recruitment in base
	 * @param Base $base
	 * @return array
	 */
	public function findByUnitsInRecruitment(Base $base): array
	{
		$query = $this->getEntityManager()->createQuery("SELECT u.id, u.name, u.array_name, u.end_recruitment, count(u) as number FROM App:Unit u
			WHERE u.base = :base AND u.in_recruitment = true
			GROUP BY u.array_name, u.end_recruitment
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

	/**
	 * method to find units that are currently in movement
	 * @param UnitMovement $unitMovement
	 * @return array
	 */
	public function findByUnitsInMovement(UnitMovement $unitMovement): array
	{
		$query = $this->getEntityManager()->createQuery("SELECT u.id, u.name, u.array_name, count(u) as number FROM App:Unit u
			WHERE u.unitMovement = :unit_movement
			GROUP BY u.array_name
		");
		$query->setParameter("unit_movement", $unitMovement, Type::OBJECT);

		$results = $query->getResult();
		$return_results = [];

		foreach ($results as $result) {
			$return_results[] = [
				"id" => $result["id"],
				"name" => $result["name"],
				"array_name" => $result["array_name"],
				"number" => $result["number"]
			];
		}

		return $return_results;
	}

	/**
	 * method that count same unit array_name in a base
	 * @param Base $base
	 * @param string $array_name
	 * @return mixed
	 * @throws NonUniqueResultException
	 */
	public function countSameUnitInBase(Base $base, string $array_name)
	{
		$query = $this->getEntityManager()->createQuery("SELECT count(u) as number FROM App:Unit u
			WHERE u.array_name = :array_name AND u.base = :base AND u.unitMovement IS NULL
		");
		$query->setParameter("array_name", $array_name, Type::STRING);
		$query->setParameter("base", $base, Type::OBJECT);

		$result = $query->getOneOrNullResult();

		if (count($result) === 1) {
			return (int)$result["number"];
		} else {
			return 0;
		}
	}

	/**
	 * method to put units on a specifiq movement
	 * @param Base $base
	 * @param UnitMovement $movement
	 * @param string $array_name
	 * @param int $number
	 * @throws DBALException
	 */
	public function putUnitsInMission(Base $base, UnitMovement $movement, string $array_name, int $number) {
		$query = $this->getEntityManager()->getConnection()->prepare("UPDATE unit u SET u.unit_movement_id = :movement_id
			WHERE u.array_name = :array_name AND u.base_id = :base_id
			LIMIT :number
		");

		$query->bindValue("array_name", $array_name, Type::STRING);
		$query->bindValue("number", $number, Type::INTEGER);
		$query->bindValue("movement_id", $movement->getId(), Type::INTEGER);
		$query->bindValue("base_id", $base->getId(), Type::INTEGER);
		$query->execute();
	}

	/**
	 * method to kill random units if there is no more food to give to all units
	 * @param Base $base
	 * @param int $number
	 * @throws DBALException
	 */
	public function killRandomUnitBecauseFood(Base $base, int $number)
	{
		$query = $this->getEntityManager()->getConnection()->prepare("DELETE FROM unit
			WHERE base_id = :base_id AND unit_movement_id IS NULL AND in_recruitment = 0
			ORDER BY RAND()
			LIMIT :number
		");

		$query->bindValue("number", $number, Type::INTEGER);
		$query->bindValue("base_id", $base->getId(), Type::INTEGER);
		$query->execute();
	}
}