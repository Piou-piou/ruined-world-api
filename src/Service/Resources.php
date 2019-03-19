<?php

namespace App\Service;

use App\Entity\Base;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class Resources
{
	/**
	 * @var EntityManagerInterface
	 */
	private $em;
	
	/**
	 * @var SessionInterface
	 */
	private $session;
	
	/**
	 * @var Globals
	 */
	private $globals;
	
	/**
	 * @var Base
	 */
	private $base;
	
	/**
	 * @var int
	 */
	private $max_warehouse_storage;
	
	/**
	 * @var int
	 */
	private $max_garner_storage;
	
	/**
	 * Resources constructor.
	 * @param EntityManagerInterface $em
	 * @param SessionInterface $session
	 * @param Globals $globals
	 */
	public function __construct(EntityManagerInterface $em, SessionInterface $session, Globals $globals)
	{
		$this->em = $em;
		$this->session = $session;
		$this->globals = $globals;
		$this->base = $globals->getCurrentBase(true);
	}
	
	/**
	 * method called to add resource
	 * @param string $resource
	 * @param int $value_to_add
	 */
	public function addResource(string $resource, int $value_to_add)
	{
		$getter = "get" . ucfirst($resource);
		$setter = "set" . ucfirst($resource);
		
		$new_resource = $this->base->$getter() + $value_to_add;
		
		if ($new_resource > $this->getWarehouseCapacity()) {
			$new_resource = $this->getWarehouseCapacity();
		}
		
		$this->base->$setter($new_resource);
		$this->em->flush();
	}
	
	/**
	 * method called to withdraw resource
	 * @param string $resource
	 * @param int $value_to_add
	 */
	public function withdrawResource(string $resource, int $value_to_add)
	{
		$getter = "get" . ucfirst($resource);
		$setter = "set" . ucfirst($resource);
		
		$new_resource = $this->base->$getter() + $value_to_add;
		
		if ($new_resource < 0) {
			$new_resource = 0;
		}
		
		$this->base->$setter($new_resource);
		$this->em->flush();
	}
	
	/**
	 * method that return maximum storage of the warehouse
	 * @return int
	 */
	public function getWarehouseCapacity(): int
	{
		return $this->getStorageCapacity("warehouse", "max_warehouse_storage");
	}
	
	/**
	 * method that return maximum storage of the warehouse
	 * @return int
	 */
	public function getGarnerCapacity(): int
	{
		return $this->getStorageCapacity("garner", "max_garner_storage");
	}
	
	/**
	 * methd that get the maximum capacity of a specific building (like warehouse of garner)
	 * @param string $building_array_name
	 * @param string $class_property (can be max_warehouse_storage or max_garner_storage)
	 * @return mixed
	 */
	private function getStorageCapacity(string $building_array_name, string $class_property)
	{
		if ($this->$class_property === null) {
			$level = 0;
			$building = $this->em->getRepository(\App\Entity\Building::class)->findOneBy([
				"base" => $this->base,
				"array_name" => $building_array_name
			]);
			
			if ($building) $level = $building->getLevel();
			
			$default_storage = $this->globals->getBuildingsConfig()[$building_array_name]["default_storage"];
			$max_storage = $this->globals->getBuildingsConfig()[$building_array_name]["max_storage"];
			$max_level = $this->globals->getBuildingsConfig()[$building_array_name]["max_level"];
			
			if ($level === 0) {
				$this->$class_property = (int)$default_storage;
			} else {
				$this->$class_property = (int)round(($max_storage * $level) / $max_level);
			}
		}
		
		return $this->$class_property;
	}
}