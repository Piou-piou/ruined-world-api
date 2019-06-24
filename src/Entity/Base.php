<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * Entity\Base
 *
 * @ORM\Entity(repositoryClass="App\Repository\BaseRepository")
 * @ORM\EntityListeners({"App\EventListener\GuidAwareListener"})
 * @ORM\Table(name="base",
 *     uniqueConstraints={
 *          @ORM\UniqueConstraint(name="guid_UNIQUE", columns={"guid"})
 *      },
 *     indexes = {
 *          @ORM\Index(name="fk_base_user_idx", columns={"user_id"})
 *     }
 *  )
 */
class Base
{
	/**
	 * @ORM\Id
	 * @ORM\Column(type="integer")
	 * @ORM\GeneratedValue(strategy="AUTO")
	 */
	protected $id;
	
	/**
	 * @ORM\Column(name="guid", type="string", length=36, nullable=false, options={"fixed"=true})
	 */
	private $guid;
	
	/**
	 * @ORM\Column(name="`name`", type="string", length=20)
	 */
	protected $name;
	
	/**
	 * @ORM\Column(type="integer")
	 */
	protected $points;
	
	/**
	 * @ORM\Column(type="json")
	 */
	protected $resources;
	
	/**
	 * @ORM\Column(type="integer")
	 */
	protected $posx;
	
	/**
	 * @ORM\Column(type="integer")
	 */
	protected $posy;
	
	/**
	 * @ORM\Column(type="datetime")
	 */
	protected $last_update_resources;
	
	/**
	 * @ORM\Column(type="datetime", nullable=true)
	 */
	protected $last_check_mission;
	
	/**
	 * @ORM\Column(type="datetime", nullable=true)
	 */
	protected $last_check_food;
	
	/**
	 * @ORM\Column(type="boolean", options={"default" : 0})
	 */
	protected $archived = 0;
	
	/**
	 * @ORM\OneToMany(targetEntity="Building", mappedBy="base")
	 * @ORM\JoinColumn(name="id", referencedColumnName="base_id", nullable=false)
	 */
	protected $buildings;
	
	/**
	 * @ORM\OneToMany(targetEntity="Mission", mappedBy="base")
	 * @ORM\JoinColumn(name="id", referencedColumnName="base_id", nullable=false)
	 */
	protected $missions;
	
	/**
	 * @ORM\OneToMany(targetEntity="Unit", mappedBy="base")
	 * @ORM\JoinColumn(name="id", referencedColumnName="base_id", nullable=false)
	 */
	protected $units;
	
	/**
	 * @ORM\OneToMany(targetEntity="UnitMovement", mappedBy="base")
	 * @ORM\JoinColumn(name="id", referencedColumnName="base_id", nullable=false)
	 */
	protected $unitMovements;

	/**
	 * @ORM\OneToMany(targetEntity="MarketMovement", mappedBy="base")
	 * @ORM\JoinColumn(name="id", referencedColumnName="base_id", nullable=false)
	 */
	protected $marketMovements;
	
	/**
	 * @ORM\ManyToOne(targetEntity="User", inversedBy="bases")
	 * @ORM\JoinColumn(name="user_id", referencedColumnName="id", nullable=false)
	 */
	protected $user;
	
	public function __construct()
	{
		$this->buildings = new ArrayCollection();
		$this->missions = new ArrayCollection();
		$this->units = new ArrayCollection();
		$this->unitMovements = new ArrayCollection();
		$this->marketMovements = new ArrayCollection();
	}
	
	/**
	 * Set the value of id.
	 *
	 * @param integer $id
	 * @return Base
	 */
	public function setId($id)
	{
		$this->id = $id;
		
		return $this;
	}
	
	/**
	 * Get the value of id.
	 *
	 * @return integer
	 */
	public function getId()
	{
		return $this->id;
	}
	
	/**
	 * @return mixed
	 */
	public function getGuid()
	{
		return $this->guid;
	}
	
	/**
	 * @param mixed $guid
	 * @return Base
	 */
	public function setGuid($guid)
	{
		$this->guid = $guid;
		
		return $this;
	}
	
	/**
	 * Set the value of name.
	 *
	 * @param string $name
	 * @return Base
	 */
	public function setName($name)
	{
		$this->name = $name;
		
		return $this;
	}
	
	/**
	 * Get the value of name.
	 *
	 * @return string
	 */
	public function getName()
	{
		return $this->name;
	}
	
	/**
	 * Set the value of points.
	 *
	 * @param integer $points
	 * @return Base
	 */
	public function setPoints($points)
	{
		$this->points = $points;
		
		return $this;
	}
	
	/**
	 * Get the value of points.
	 *
	 * @return integer
	 */
	public function getPoints()
	{
		return $this->points;
	}
	
	/**
	 * Set the value of resources.
	 *
	 * @param string $resources
	 * @return Base
	 */
	public function setResources($resources)
	{
		$this->resources = $resources;
		
		return $this;
	}
	
	/**
	 * Get the value of resources.
	 *
	 * @return string
	 */
	public function getResources()
	{
		return $this->resources;
	}
	
	/**
	 * Set the value of posx.
	 *
	 * @param integer $posx
	 * @return Base
	 */
	public function setPosx($posx)
	{
		$this->posx = $posx;
		
		return $this;
	}
	
	/**
	 * Get the value of posx.
	 *
	 * @return integer
	 */
	public function getPosx()
	{
		return $this->posx;
	}
	
	/**
	 * Set the value of posy.
	 *
	 * @param integer $posy
	 * @return Base
	 */
	public function setPosy($posy)
	{
		$this->posy = $posy;
		
		return $this;
	}
	
	/**
	 * Get the value of posy.
	 *
	 * @return integer
	 */
	public function getPosy()
	{
		return $this->posy;
	}
	
	/**
	 * Set the value of last_update_resources.
	 *
	 * @param \DateTime $last_update_resources
	 * @return Base
	 */
	public function setLastUpdateResources($last_update_resources)
	{
		$this->last_update_resources = $last_update_resources;
		
		return $this;
	}
	
	/**
	 * Get the value of last_update_resources.
	 *
	 * @return \DateTime
	 */
	public function getLastUpdateResources()
	{
		return $this->last_update_resources;
	}
	
	/**
	 * Set the value of last_check_mission.
	 *
	 * @param \DateTime $last_check_mission
	 * @return Base
	 */
	public function setLastCheckMission($last_check_mission)
	{
		$this->last_check_mission = $last_check_mission;
		
		return $this;
	}
	
	/**
	 * Get the value of last_check_mission.
	 *
	 * @return \DateTime
	 */
	public function getLastCheckMission()
	{
		return $this->last_check_mission;
	}
	
	/**
	 * Set the value of last_check_food.
	 *
	 * @param \DateTime $last_check_food
	 * @return Base
	 */
	public function setLastCheckFood($last_check_food)
	{
		$this->last_check_food = $last_check_food;
		
		return $this;
	}
	
	/**
	 * Get the value of last_check_food.
	 *
	 * @return \DateTime
	 */
	public function getLastCheckFood()
	{
		return $this->last_check_food;
	}
	
	/**
	 * @return mixed
	 */
	public function getArchived()
	{
		return $this->archived;
	}
	
	/**
	 * @param mixed $archived
	 */
	public function setArchived($archived): void
	{
		$this->archived = $archived;
	}
	
	/**
	 * Add Building entity to collection (one to many).
	 *
	 * @param Building $building
	 * @return Base
	 */
	public function addBuilding(Building $building)
	{
		$this->buildings[] = $building;
		
		return $this;
	}
	
	/**
	 * Remove Building entity from collection (one to many).
	 *
	 * @param Building $building
	 * @return Base
	 */
	public function removeBuilding(Building $building)
	{
		$this->buildings->removeElement($building);
		
		return $this;
	}
	
	/**
	 * Get Building entity collection (one to many).
	 *
	 * @return \Doctrine\Common\Collections\Collection
	 */
	public function getBuildings()
	{
		return $this->buildings;
	}
	
	/**
	 * Add Mission entity to collection (one to many).
	 *
	 * @param Mission $mission
	 * @return Base
	 */
	public function addMission(Mission $mission)
	{
		$this->missions[] = $mission;
		
		return $this;
	}
	
	/**
	 * Remove Mission entity from collection (one to many).
	 *
	 * @param Mission $mission
	 * @return Base
	 */
	public function removeMission(Mission $mission)
	{
		$this->missions->removeElement($mission);
		
		return $this;
	}
	
	/**
	 * Get Mission entity collection (one to many).
	 *
	 * @return \Doctrine\Common\Collections\Collection
	 */
	public function getMissions()
	{
		return $this->missions;
	}
	
	/**
	 * Add Unit entity to collection (one to many).
	 *
	 * @param Unit $unit
	 * @return Base
	 */
	public function addUnit(Unit $unit)
	{
		$this->units[] = $unit;
		
		return $this;
	}
	
	/**
	 * Remove Unit entity from collection (one to many).
	 *
	 * @param Unit $unit
	 * @return Base
	 */
	public function removeUnit(Unit $unit)
	{
		$this->units->removeElement($unit);
		
		return $this;
	}
	
	/**
	 * Get Unit entity collection (one to many).
	 *
	 * @return \Doctrine\Common\Collections\Collection
	 */
	public function getUnits()
	{
		return $this->units;
	}
	
	/**
	 * Add UnitMovement entity to collection (one to many).
	 *
	 * @param UnitMovement $unitMovement
	 * @return Base
	 */
	public function addUnitMovement(UnitMovement $unitMovement)
	{
		$this->unitMovements[] = $unitMovement;
		
		return $this;
	}
	
	/**
	 * Remove UnitMovement entity from collection (one to many).
	 *
	 * @param UnitMovement $unitMovement
	 * @return Base
	 */
	public function removeUnitMovement(UnitMovement $unitMovement)
	{
		$this->unitMovements->removeElement($unitMovement);
		
		return $this;
	}

	/**
	 * Get UnitMovement entity collection (one to many).
	 *
	 * @return \Doctrine\Common\Collections\Collection
	 */
	public function getUnitMovements()
	{
		return $this->unitMovements;
	}

	/**
	 * Add MarketMovement entity to collection (one to many).
	 *
	 * @param MarketMovement $marketMovement
	 * @return Base
	 */
	public function addMarketMovement(MarketMovement $marketMovement)
	{
		$this->marketMovements[] = $marketMovement;

		return $this;
	}

	/**
	 * Remove MarketMovement entity from collection (one to many).
	 *
	 * @param MarketMovement $marketMovement
	 * @return Base
	 */
	public function removeMarketMovement(MarketMovement $marketMovement)
	{
		$this->marketMovements->removeElement($marketMovement);

		return $this;
	}

	/**
	 * Get MarketMovement entity collection (one to many).
	 *
	 * @return \Doctrine\Common\Collections\Collection
	 */
	public function getMarketMovements()
	{
		return $this->marketMovements;
	}
	
	/**
	 * Set User entity (many to one).
	 *
	 * @param User $user
	 * @return Base
	 */
	public function setUser(User $user = null)
	{
		$this->user = $user;
		
		return $this;
	}
	
	/**
	 * Get User entity (many to one).
	 *
	 * @return User
	 */
	public function getUser()
	{
		return $this->user;
	}
	
	/**
	 * @return int
	 */
	public function getElectricity(): int
	{
		if (is_array($this->resources) && array_key_exists("electricity", $this->resources)) {
			return $this->resources["electricity"];
		}
		
		return 0;
	}
	
	/**
	 * @param int $electricity
	 * @return Base
	 */
	public function setElectricity(int $electricity): Base
	{
		$this->resources["electricity"] = $electricity;
		
		return $this;
	}
	
	/**
	 * @return int
	 */
	public function getFuel(): int
	{
		if (is_array($this->resources) && array_key_exists("fuel", $this->resources)) {
			return $this->resources["fuel"];
		}
		
		return 0;
	}
	
	/**
	 * @param int $fuel
	 * @return Base
	 */
	public function setFuel(int $fuel): Base
	{
		$this->resources["fuel"] = $fuel;
		
		return $this;
	}
	
	/**
	 * @return int
	 */
	public function getIron(): int
	{
		if (is_array($this->resources) && array_key_exists("iron", $this->resources)) {
			return $this->resources["iron"];
		}
		
		return 0;
	}
	
	/**
	 * @param int $iron
	 * @return Base
	 */
	public function setIron(int $iron): Base
	{
		$this->resources["iron"] = $iron;
		
		return $this;
	}
	
	/**
	 * @return int
	 */
	public function getWater(): int
	{
		if (is_array($this->resources) && array_key_exists("water", $this->resources)) {
			return $this->resources["water"];
		}
		
		return 0;
	}
	
	/**
	 * @param int $water
	 * @return Base
	 */
	public function setWater(int $water): Base
	{
		$this->resources["water"] = $water;
		
		return $this;
	}
	
	/**
	 * @return int
	 */
	public function getFood(): int
	{
		if (is_array($this->resources) && array_key_exists("food", $this->resources)) {
			return $this->resources["food"];
		}
		
		return 0;
	}
	
	/**
	 * @param int $food
	 * @return Base
	 */
	public function setFood(int $food): Base
	{
		$this->resources["food"] = $food;
		
		return $this;
	}
}