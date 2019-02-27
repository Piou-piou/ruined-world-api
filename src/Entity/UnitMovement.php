<?php

namespace Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * Entity\UnitMovement
 *
 * @ORM\Entity(repositoryClass="UnitMovementRepository")
 * @ORM\Table(name="unit_movement", indexes={@ORM\Index(name="fk_unit_movement_base1_idx", columns={"base_id"})})
 */
class UnitMovement
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     */
    protected $id;

    /**
     * @ORM\Column(type="datetime")
     */
    protected $end_date;

    /**
     * @ORM\Column(name="`type`", type="integer")
     */
    protected $type;

    /**
     * @ORM\Column(type="integer")
     */
    protected $base_id;

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
     * @return \Entity\UnitMovement
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
     * @return \Entity\UnitMovement
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
     * Set the value of type.
     *
     * @param integer $type
     * @return \Entity\UnitMovement
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
     * Set the value of base_id.
     *
     * @param integer $base_id
     * @return \Entity\UnitMovement
     */
    public function setBaseId($base_id)
    {
        $this->base_id = $base_id;

        return $this;
    }

    /**
     * Get the value of base_id.
     *
     * @return integer
     */
    public function getBaseId()
    {
        return $this->base_id;
    }

    /**
     * Add Mission entity to collection (one to many).
     *
     * @param \Entity\Mission $mission
     * @return \Entity\UnitMovement
     */
    public function addMission(Mission $mission)
    {
        $this->missions[] = $mission;

        return $this;
    }

    /**
     * Remove Mission entity from collection (one to many).
     *
     * @param \Entity\Mission $mission
     * @return \Entity\UnitMovement
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
     * @param \Entity\Unit $unit
     * @return \Entity\UnitMovement
     */
    public function addUnit(Unit $unit)
    {
        $this->units[] = $unit;

        return $this;
    }

    /**
     * Remove Unit entity from collection (one to many).
     *
     * @param \Entity\Unit $unit
     * @return \Entity\UnitMovement
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
     * @param \Entity\UnitGroup $unitGroup
     * @return \Entity\UnitMovement
     */
    public function addUnitGroup(UnitGroup $unitGroup)
    {
        $this->unitGroups[] = $unitGroup;

        return $this;
    }

    /**
     * Remove UnitGroup entity from collection (one to many).
     *
     * @param \Entity\UnitGroup $unitGroup
     * @return \Entity\UnitMovement
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
     * @param \Entity\Base $base
     * @return \Entity\UnitMovement
     */
    public function setBase(Base $base = null)
    {
        $this->base = $base;

        return $this;
    }

    /**
     * Get Base entity (many to one).
     *
     * @return \Entity\Base
     */
    public function getBase()
    {
        return $this->base;
    }
}