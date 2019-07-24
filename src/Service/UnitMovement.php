<?php

namespace App\Service;

use App\Entity\Base;
use DateInterval;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Exception;

class UnitMovement
{
	/**
	 * @var EntityManagerInterface
	 */
	private $em;

	/**
	 * @var Api
	 */
	private $api;

	/**
	 * @var Globals
	 */
	private $globals;

	/**
	 * @var Mission
	 */
	private $mission;

	/**
	 * @var Fight
	 */
	private $fight;

	/**
	 * UnitMovement constructor.
	 * @param EntityManagerInterface $em
	 * @param Api $api
	 * @param Globals $globals
	 * @param Mission $mission
	 * @param Fight $fight
	 */
	public function __construct(EntityManagerInterface $em, Api $api, Globals $globals, Mission $mission, Fight $fight)
	{
		$this->em = $em;
		$this->api = $api;
		$this->globals = $globals;
		$this->mission = $mission;
		$this->fight = $fight;
	}

	/**
	 * method that get entity of current movement based on the type and type id of it
	 * @param int $type
	 * @param int $type_id
	 * @return \App\Entity\Mission|Base|null
	 */
	private function getEntityOfTypeMovement(int $type, int $type_id)
	{
		$entity = null;
		if ($type === \App\Entity\UnitMovement::TYPE_MISSION) {
			$entity = \App\Entity\Mission::class;
		} else if ($type === \App\Entity\UnitMovement::TYPE_ATTACK) {
			$entity = Base::class;
		}

		if (!$entity) {
			return null;
		}

		return $this->em->getRepository($entity)->find($type_id);
	}

	/**
	 * method that create a unit movement
	 * @param int $type
	 * @param int $type_id
	 * @param int $movement_type
	 * @param int $config_id
	 * @param int $speed
	 * @return \App\Entity\UnitMovement
	 * @throws Exception
	 */
	public function create(int $type, int $type_id, int $movement_type, int $config_id = null, int $speed = 1): \App\Entity\UnitMovement
	{
		$now = new DateTime();
		if ($type === \App\Entity\UnitMovement::TYPE_MISSION) {
			$mission_config = $this->globals->getMissionsConfig()[$config_id];
			$duration = $mission_config["duration"];
		} else {
			$base_dest = $this->em->getRepository(Base::class)->find($type_id);
			$duration = $this->globals->getTimeToTravel($this->globals->getCurrentBase(), $base_dest, $speed);
		}

		$unit_movement = new \App\Entity\UnitMovement();
		$unit_movement->setBase($this->globals->getCurrentBase());
		$unit_movement->setDuration($duration);
		$unit_movement->setEndDate($now->add(new DateInterval("PT". $duration ."S")));
		$unit_movement->setType($type);
		$unit_movement->setTypeId($type_id);
		$unit_movement->setMovementType($movement_type);
		$this->em->persist($unit_movement);
		$this->em->flush();

		return $unit_movement;
	}

	/**
	 * method that return current units movements in base
	 * @return array
	 * @throws Exception
	 */
	public function getCurrentMovementsInBase(): array
	{
		$this->updateUnitMovement($this->globals->getCurrentBase());
		$unit_movements = $this->em->getRepository(\App\Entity\UnitMovement::class)->findMovementsByBase($this->globals->getCurrentBase());
		$return_movements = [];

		foreach ($unit_movements as $unit_movement) {
			$entity_type = $this->getEntityOfTypeMovement($unit_movement->getType(), $unit_movement->getTypeId());
			$name = "";
			if ($entity_type instanceof Base) {
				$name = $entity_type->getName();
			}

			$units = [];
			if ($unit_movement->getBase() === $this->globals->getCurrentBase()) {
				$units = $this->em->getRepository(\App\Entity\UnitMovement::class)->findByUnitsInMovement($unit_movement);
			}

			$return_movements[] = [
				"end_date" => $unit_movement->getEndDate()->getTimestamp(),
				"string_type" => $unit_movement->getStringType(),
				"entity_name" => $name,
				"base_id" => $unit_movement->getBase()->getId(),
				"movement_type_string" => $unit_movement->getStringMovementType(),
				"units" => $units
			];
		}

		return $return_movements;
	}

	/**
	 * method called to update unit movements of the base
	 * @param Base $base
	 * @throws Exception
	 */
	public function updateUnitMovement(Base $base)
	{
		$em = $this->em;
		$unit_movements_ended = $em->getRepository(\App\Entity\UnitMovement::class)->findByMovementEnded($base);

		/** @var \App\Entity\UnitMovement $unit_movement */
		foreach ($unit_movements_ended as $unit_movement) {
			if ($unit_movement->getType() === \App\Entity\UnitMovement::TYPE_ATTACK && $unit_movement->getMovementType() === \App\Entity\UnitMovement::MOVEMENT_TYPE_GO) {
				$this->fight->attackBase($base, $unit_movement, $this->getEntityOfTypeMovement($unit_movement->getType(), $unit_movement->getTypeId()));
			} else if ($unit_movement->getType() === \App\Entity\UnitMovement::TYPE_ATTACK && $unit_movement->getMovementType() === \App\Entity\UnitMovement::MOVEMENT_TYPE_RETURN) {
				$unit_movement->clearUnits();
				$this->em->persist($unit_movement);
				$this->em->flush();
				$this->em->remove($unit_movement);
				$this->em->flush();
			} else if ($unit_movement->getType() === \App\Entity\UnitMovement::TYPE_MISSION) {
				$this->mission->endMission($base, $unit_movement, $this->getEntityOfTypeMovement($unit_movement->getType(), $unit_movement->getTypeId()));
			}
		}
	}
}