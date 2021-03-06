<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * Entity\UnitMovement
 *
 * @ORM\Entity(repositoryClass="App\Repository\UnitMovementRepository")
 * @ORM\Table(name="unit_movement", indexes={@ORM\Index(name="fk_unit_movement_base1_idx", columns={"base_id"})})
 */
class UnitMovement
{
	const TYPE_MISSION = 1,
		TYPE_ATTACK = 2;

	const MOVEMENT_TYPE_GO = 0,
		MOVEMENT_TYPE_RETURN = 1,
		MOVEMENT_TYPE_MISSION = 2;

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
	 * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\Column(type="datetime")
     */
    protected $end_date;

	/**
	 * @ORM\Column(type="integer", nullable=false)
	 */
	protected $duration;

    /**
     * @ORM\Column(name="`type`", type="integer")
     */
    protected $type;

	/**
	 * @ORM\Column(name="type_id", type="integer")
	 */
    protected $type_id;

	/**
	 * @ORM\Column(name="movement_type", type="integer")
	 */
    protected $movement_type;

	/**
	 * @ORM\Column(type="json", nullable=true)
	 */
	protected $resources;

    /**
     * @ORM\OneToMany(targetEntity="Mission", mappedBy="unitMovement")
     * @ORM\JoinColumn(name="id", referencedColumnName="unit_movement_id", nullable=false)
     */
    protected $missions;

    /**
     * @ORM\OneToMany(targetEntity="Unit", mappedBy="unitMovement")
     * @ORM\JoinColumn(name="id", referencedColumnName="unit_movement_id", nullable=false)
     */
    protected $units;

    /**
     * @ORM\OneToMany(targetEntity="UnitGroup", mappedBy="unitMovement")
     * @ORM\JoinColumn(name="id", referencedColumnName="unit_movement_id", nullable=false)
     */
    protected $unitGroups;

    /**
     * @ORM\ManyToOne(targetEntity="Base", inversedBy="unitMovements")
     * @ORM\JoinColumn(name="base_id", referencedColumnName="id", nullable=false)
     */
    protected $base;

    public function __construct()
    {
        $this->missions = new ArrayCollection();
        $this->units = new ArrayCollection();
        $this->unitGroups = new ArrayCollection();
    }

    /**
     * Set the value of id.
     *
     * @param integer $id
     * @return UnitMovement
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
     * Set the value of end_date.
     *
     * @param \DateTime $end_date
     * @return UnitMovement
     */
    public function setEndDate($end_date)
    {
        $this->end_date = $end_date;

        return $this;
    }

    /**
     * Get the value of end_date.
     *
     * @return \DateTime
     */
    public function getEndDate()
    {
        return $this->end_date;
    }

	/**
	 * @return mixed
	 */
	public function getDuration()
	{
		return $this->duration;
	}

	/**
	 * @param mixed $duration
	 * @return UnitMovement
	 */
	public function setDuration($duration)
	{
		$this->duration = $duration;

		return $this;
	}

    /**
     * Set the value of type.
     *
     * @param integer $type
     * @return UnitMovement
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Get the value of type.
     *
     * @return integer
     */
    public function getType()
    {
        return $this->type;
    }

	/**
	 * @return mixed
	 */
	public function getTypeId()
	{
		return $this->type_id;
	}

	/**
	 * @param mixed $type_id
	 * @return UnitMovement
	 */
	public function setTypeId($type_id)
	{
		$this->type_id = $type_id;

		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getMovementType()
	{
		return $this->movement_type;
	}

	/**
	 * @param mixed $movement_type
	 * @return UnitMovement
	 */
	public function setMovementType($movement_type)
	{
		$this->movement_type = $movement_type;

		return $this;
	}

	/**
	 * Set the value of resources.
	 *
	 * @param string $resources
	 * @return UnitMovement
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
     * Add Mission entity to collection (one to many).
     *
     * @param Mission $mission
     * @return UnitMovement
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
     * @return UnitMovement
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
     * @return UnitMovement
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
     * @return UnitMovement
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
     * Add UnitGroup entity to collection (one to many).
     *
     * @param UnitGroup $unitGroup
     * @return UnitMovement
     */
    public function addUnitGroup(UnitGroup $unitGroup)
    {
        $this->unitGroups[] = $unitGroup;

        return $this;
    }

    /**
     * Remove UnitGroup entity from collection (one to many).
     *
     * @param UnitGroup $unitGroup
     * @return UnitMovement
     */
    public function removeUnitGroup(UnitGroup $unitGroup)
    {
        $this->unitGroups->removeElement($unitGroup);

        return $this;
    }

    /**
     * Get UnitGroup entity collection (one to many).
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getUnitGroups()
    {
        return $this->unitGroups;
    }

    /**
     * Set Base entity (many to one).
     *
     * @param Base $base
     * @return UnitMovement
     */
    public function setBase(Base $base = null)
    {
        $this->base = $base;

        return $this;
    }

    /**
     * Get Base entity (many to one).
     *
     * @return Base
     */
    public function getBase()
    {
        return $this->base;
    }

	/**
	 * @return string
	 */
    public function getStringType()
	{
		if ($this->getType() === self::TYPE_MISSION) {
			return "mission";
		} else if ($this->getType() === self::TYPE_ATTACK) {
			return "attack";
		}
	}

	/**
	 * @return string
	 */
	public function getStringMovementType()
	{
		if ($this->getMovementType() === self::MOVEMENT_TYPE_GO) {
			return "go";
		} else if ($this->getMovementType() === self::MOVEMENT_TYPE_RETURN) {
			return "return";
		} else {
			return "mission";
		}
	}

	public function clearUnits()
	{
		/** @var Unit $unit */
		foreach ($this->getUnits() as $unit) {
			$unit->setUnitMovement(null);
		}
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
	 * @return UnitMovement
	 */
	public function setElectricity(int $electricity): UnitMovement
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
	 * @return UnitMovement
	 */
	public function setFuel(int $fuel): UnitMovement
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
	 * @return UnitMovement
	 */
	public function setIron(int $iron): UnitMovement
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
	 * @return UnitMovement
	 */
	public function setWater(int $water): UnitMovement
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
	 * @return UnitMovement
	 */
	public function setFood(int $food): UnitMovement
	{
		$this->resources["food"] = $food;

		return $this;
	}
}