<?php

namespace App\Service;

use App\Entity\Base;
use App\Entity\UnitMovement;
use Doctrine\ORM\EntityManagerInterface;

class Unit
{
	/**
	 * @var EntityManagerInterface
	 */
	private $em;

	/**
	 * @var Globals
	 */
	private $globals;

	/**
	 * Unit constructor.
	 * @param EntityManagerInterface $em
	 * @param Globals $globals
	 */
	public function __construct(EntityManagerInterface $em, Globals $globals)
	{
		$this->em = $em;
		$this->globals = $globals;
	}

	/**
	 * method that get entity of current movement based on the type and type id of it
	 * @param int $type
	 * @param int $type_id
	 * @return object|null
	 */
	private function getEntityOfTypeMovement(int $type, int $type_id)
	{
		$entity = null;
		if ($type === UnitMovement::TYPE_MISSION) {
			$entity = \App\Entity\Mission::class;
		}

		if (!$entity) {
			return null;
		}

		return $this->em->getRepository($entity)->find($type_id);
	}

	/**
	 * method that return current units movements in base
	 */
	public function getCurrentMovementsInBase()
	{
		$unit_movements = $this->em->getRepository(UnitMovement::class)->findBy([
			"base" => $this->globals->getCurrentBase()
		]);
		$return_movements = [];

		foreach ($unit_movements as $unit_movement) {
			$entity_type = $this->getEntityOfTypeMovement($unit_movement->getType(), $unit_movement->getTypeId());

			$return_movements[] = [
				"duration" => $unit_movement->getDuration(),
				"end_date" => $unit_movement->getEndDate()->getTimestamp(),
				"type" => $unit_movement->getType(),
				"string_type" => $unit_movement->getStringType(),
				"entity_type" => $entity_type,
				"movement_type" => $unit_movement->getType(),
				"units" => $this->em->getRepository(UnitMovement::class)->findByUnitsInMovement($unit_movement)
			];
		}

		return $return_movements;
	}
}