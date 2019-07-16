<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * Entity\UnitGroup
 *
 * @ORM\Entity
 * @ORM\Table(name="unit_group", indexes={@ORM\Index(name="fk_unit_group_unit_movement1_idx", columns={"unit_movement_id"})})
 */
class UnitGroup
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     */
    protected $id;

    /**
     * @ORM\Column(name="`name`", type="string", length=45)
     */
    protected $name;

    /**
     * @ORM\Column(type="integer")
     */
    protected $experience;

	/**
	 * @ORM\OneToMany(targetEntity="Unit", mappedBy="unitGroup")
	 * @ORM\JoinColumn(name="id", referencedColumnName="group_id", nullable=false)
	 */
    protected $units;

    /**
     * @ORM\ManyToOne(targetEntity="UnitMovement", inversedBy="unitGroups")
     * @ORM\JoinColumn(name="unit_movement_id", referencedColumnName="id", nullable=false)
     */
    protected $unitMovement;

	public function __construct()
	{
		$this->units = new ArrayCollection();
	}

    /**
     * Set the value of id.
     *
     * @param integer $id
     * @return UnitGroup
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
     * Set the value of name.
     *
     * @param string $name
     * @return UnitGroup
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
     * Set the value of experience.
     *
     * @param integer $experience
     * @return UnitGroup
     */
    public function setExperience($experience)
    {
        $this->experience = $experience;

        return $this;
    }

    /**
     * Get the value of experience.
     *
     * @return integer
     */
    public function getExperience()
    {
        return $this->experience;
    }

	/**
	 * Add Unit entity to collection (one to many).
	 *
	 * @param Unit $unit
	 * @return UnitGroup
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
	 * @return UnitGroup
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
     * Set UnitMovement entity (many to one).
     *
     * @param UnitMovement $unitMovement
     * @return UnitGroup
     */
    public function setUnitMovement(UnitMovement $unitMovement = null)
    {
        $this->unitMovement = $unitMovement;

        return $this;
    }

    /**
     * Get UnitMovement entity (many to one).
     *
     * @return UnitMovement
     */
    public function getUnitMovement()
    {
        return $this->unitMovement;
    }
}