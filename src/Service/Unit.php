<?php

namespace App\Service;

use App\Entity\Base;
use App\Entity\UnitMovement;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\NonUniqueResultException;

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
	 * @var Resources
	 */
	private $resources;

	/**
	 * Unit constructor.
	 * @param EntityManagerInterface $em
	 * @param Globals $globals
	 * @param Resources $resources
	 */
	public function __construct(EntityManagerInterface $em, Globals $globals, Resources $resources)
	{
		$this->em = $em;
		$this->globals = $globals;
		$this->resources = $resources;
	}

	/**
	 * method that return max transport weight that unit can carry durin movement
	 * @param $units
	 * @return int
	 */
	private function getMaxCapacityTransport($units): int
	{
		$max_transport_weight = 0;

		/** @var \App\Entity\Unit $unit */
		foreach ($units as $unit) {
			$max_transport_weight += $this->globals->getUnitsConfig()[$unit->getArrayName()]["transport_weight"];
		}

		return $max_transport_weight;
	}

	/**
	 * method that test if we have enough unit of a type in our base
	 * @param array $units
	 * @return bool
	 * @throws NonUniqueResultException
	 */
	public function testEnoughUnitInBaseToSend(array $units)
	{
		foreach ($units as $array_name => $number) {
			$unit_base = $this->em->getRepository(\App\Entity\Unit::class)->countSameUnitInBase($this->globals->getCurrentBase(),$array_name);
			if ($unit_base < $number) {
				return false;
			}
		}

		return true;
	}

	/**
	 * method that get entity of current movement based on the type and type id of it
	 * @param int $type
	 * @param int $type_id
	 * @return \App\Entity\Mission|null
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

	/**
	 * method called to update unit movements of the base
	 * @param Base $base
	 * @throws \Exception
	 */
	public function updateUnitMovement(Base $base)
	{
		$em = $this->em;
		$unit_movements_ended = $em->getRepository(UnitMovement::class)->findByMovementEnded($base);

		/** @var UnitMovement $unit_movement */
		foreach ($unit_movements_ended as $unit_movement) {
			if ($unit_movement->getType() === UnitMovement::TYPE_ATTACK && $unit_movement->getMovementType() === UnitMovement::MOVEMENT_TYPE_GO) {
				// attack on the go
			} else if ($unit_movement->getType() === UnitMovement::TYPE_ATTACK && $unit_movement->getMovementType() === UnitMovement::MOVEMENT_TYPE_RETURN) {
				// attack on the return
			} else if ($unit_movement->getType() === UnitMovement::TYPE_MISSION) {
				$this->endMission($base, $unit_movement, $this->getEntityOfTypeMovement($unit_movement->getType(), $unit_movement->getTypeId()));
			}
		}
	}

	/**
	 * method called to end a mission kill unit based on lost percentage of it and give food based
	 * on win_resources percentage of mission
	 * @param Base $base
	 * @param UnitMovement $unit_movement
	 * @param \App\Entity\Mission $mission
	 */
	private function endMission(Base $base, UnitMovement $unit_movement, \App\Entity\Mission $mission)
	{
		$current_mission_config = $this->globals->getMissionsConfig()[$mission->getMissionsConfigId()];
		$lost_unit = round(count($unit_movement->getUnits())*(rand(0, $current_mission_config["lost_percentage"])/100));

		for ($i=0 ; $i<$lost_unit ; $i++) {
			$this->em->remove($unit_movement->getUnits()->get($i));
			$unit_movement->getUnits()->remove($i);
		}
		$this->em->persist($unit_movement);
		$this->em->flush();

		$max_transport_capacity = $this->getMaxCapacityTransport($unit_movement->getUnits());
		$win_resources = round($max_transport_capacity*((100-$current_mission_config["win_resources"])/100));

		$this->resources->setBase($base);
		$this->resources->addResource("food", $win_resources);

		$mission->setInProgress(false);
		$mission->setUnitMovement(null);
		$this->em->persist($mission);
		$unit_movement->clearUnits();
		$this->em->persist($unit_movement);
		$this->em->flush();
		$this->em->remove($unit_movement);
	}
}