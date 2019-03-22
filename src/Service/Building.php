<?php

namespace App\Service;

use Doctrine\ORM\EntityManagerInterface;

class Building
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
	 * Building constructor.
	 * @param Globals $globals
	 */
	public function __construct(EntityManagerInterface $em, Globals $globals)
	{
		$this->globals = $globals;
		$this->em = $em;
	}
	
	/**
	 * this method return the construction time of a building
	 * @param string $array_name
	 * @param int $level
	 * @return int
	 */
	public function getConstructionTime(string $array_name, int $level): int
	{
		$building_config = $this->globals->getBuildingsConfig()[$array_name];
		
		if ($level === 0) {
			return (int)$building_config["construction_time"];
		} else {
			return (int)round($building_config["construction_time"] * $level);
		}
	}
	
	/**
	 * method that end all construction that are terminated
	 */
	public function endConstructionBuildingsInBase()
	{
		$buildings = $this->em->getRepository(\App\Entity\Building::class)->finByBuildingInConstruction($this->globals->getCurrentBase());
		
		/**
		 * @var $building \App\Entity\Building
		 */
		foreach ($buildings as $building) {
			$building->setLevel($building->getLevel() + 1);
			$building->setInConstruction(false);
			$building->setEndConstruction(null);
			$this->em->persist($building);
		}
		
		$this->em->flush();
	}
}