<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * Entity\Unit
 *
 * @ORM\Entity
 * @ORM\Table(name="unit", indexes={@ORM\Index(name="fk_unit_base1_idx", columns={"base_id"}), @ORM\Index(name="fk_unit_unit_movement1_idx", columns={"unit_movement_id"})})
 */
class Unit
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\Column(name="`name`", type="string", length=45)
     */
    protected $name;

    /**
     * @ORM\Column(type="string", length=45)
     */
    protected $array_name;

    /**
     * @ORM\Column(type="integer")
     */
    protected $assault_level;

    /**
     * @ORM\Column(type="integer")
     */
    protected $defense_level;
    /**
     * @ORM\OneToMany(targetEntity="UnitGroup", mappedBy="unit")
     * @ORM\JoinColumn(name="id", referencedColumnName="unit_id", nullable=false)
     */
    protected $unitGroups;

    /**
     * @ORM\ManyToOne(targetEntity="Base", inversedBy="units")
     * @ORM\JoinColumn(name="base_id", referencedColumnName="id", nullable=false)
     */
    protected $base;

    /**
     * @ORM\ManyToOne(targetEntity="UnitMovement", inversedBy="units")
     * @ORM\JoinColumn(name="unit_movement_id", referencedColumnName="id", nullable=false)
     */
    protected $unitMovement;

    public function __construct()
    {
        $this->unitGroups = new ArrayCollection();
    }

    /**
     * Set the value of id.
     *
     * @param integer $id
     * @return Unit
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
     * @return Unit
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
     * Set the value of array_name.
     *
     * @param string $array_name
     * @return Unit
     */
    public function setArrayName($array_name)
    {
        $this->array_name = $array_name;

        return $this;
    }

    /**
     * Get the value of array_name.
     *
     * @return string
     */
    public function getArrayName()
    {
        return $this->array_name;
    }

    /**
     * Set the value of assault_level.
     *
     * @param integer $assault_level
     * @return Unit
     */
    public function setAssaultLevel($assault_level)
    {
        $this->assault_level = $assault_level;

        return $this;
    }

    /**
     * Get the value of assault_level.
     *
     * @return integer
     */
    public function getAssaultLevel()
    {
        return $this->assault_level;
    }

    /**
     * Set the value of defense_level.
     *
     * @param integer $defense_level
     * @return Unit
     */
    public function setDefenseLevel($defense_level)
    {
        $this->defense_level = $defense_level;

        return $this;
    }

    /**
     * Get the value of defense_level.
     *
     * @return integer
     */
    public function getDefenseLevel()
    {
        return $this->defense_level;
    }

    /**
     * Add UnitGroup entity to collection (one to many).
     *
     * @param UnitGroup $unitGroup
     * @return Unit
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
     * @return Unit
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
     * @return Unit
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
     * Set UnitMovement entity (many to one).
     *
     * @param UnitMovement $unitMovement
     * @return Unit
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