<?php

namespace App\Service;

class Building
{
	/**
	 * @var Globals
	 */
	private $globals;
	
	/**
	 * Building constructor.
	 * @param Globals $globals
	 */
	public function __construct(Globals $globals)
	{
		$this->globals = $globals;
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
}