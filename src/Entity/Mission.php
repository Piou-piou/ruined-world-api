<?php

namespace Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Entity\Mission
 *
 * @ORM\Entity(repositoryClass="MissionRepository")
 * @ORM\Table(name="mission", indexes={@ORM\Index(name="fk_mission_base1_idx", columns={"base_id"}), @ORM\Index(name="fk_mission_unit_movement1_idx", columns={"unit_movement_id"})})
 */
class Mission
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\Column(name="`name`", type="string", length=255)
     */
    protected $name;

    /**
     * @ORM\Column(type="text")
     */
    protected $description;

    /**
     * @ORM\Column(type="integer")
     */
    protected $points;

    /**
     * @ORM\Column(type="integer")
     */
    protected $duration;

    /**
     * @ORM\Column(type="boolean")
     */
    protected $in_progress;

    /**
     * @ORM\Column(type="integer")
     */
    protected $base_id;

    /**
     * @ORM\Column(type="integer")
     */
    protected $unit_movement_id;

    /**
     * @ORM\ManyToOne(targetEntity="Base", inversedBy="missions")
     * @ORM\JoinColumn(name="base_id", referencedColumnName="id", nullable=false)
     */
    protected $base;

    /**
     * @ORM\ManyToOne(targetEntity="UnitMovement", inversedBy="missions")
     * @ORM\JoinColumn(name="unit_movement_id", referencedColumnName="id", nullable=false)
     */
    protected $unitMovement;

    public function __construct()
    {
    }

    /**
     * Set the value of id.
     *
     * @param integer $id
     * @return \Entity\Mission
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
     * @return \Entity\Mission
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
     * Set the value of description.
     *
     * @param string $description
     * @return \Entity\Mission
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Get the value of description.
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Set the value of points.
     *
     * @param integer $points
     * @return \Entity\Mission
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
     * Set the value of duration.
     *
     * @param integer $duration
     * @return \Entity\Mission
     */
    public function setDuration($duration)
    {
        $this->duration = $duration;

        return $this;
    }

    /**
     * Get the value of duration.
     *
     * @return integer
     */
    public function getDuration()
    {
        return $this->duration;
    }

    /**
     * Set the value of in_progress.
     *
     * @param boolean $in_progress
     * @return \Entity\Mission
     */
    public function setInProgress($in_progress)
    {
        $this->in_progress = $in_progress;

        return $this;
    }

    /**
     * Get the value of in_progress.
     *
     * @return boolean
     */
    public function getInProgress()
    {
        return $this->in_progress;
    }

    /**
     * Set the value of base_id.
     *
     * @param integer $base_id
     * @return \Entity\Mission
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
     * Set the value of unit_movement_id.
     *
     * @param integer $unit_movement_id
     * @return \Entity\Mission
     */
    public function setUnitMovementId($unit_movement_id)
    {
        $this->unit_movement_id = $unit_movement_id;

        return $this;
    }

    /**
     * Get the value of unit_movement_id.
     *
     * @return integer
     */
    public function getUnitMovementId()
    {
        return $this->unit_movement_id;
    }

    /**
     * Set Base entity (many to one).
     *
     * @param \Entity\Base $base
     * @return \Entity\Mission
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

    /**
     * Set UnitMovement entity (many to one).
     *
     * @param \Entity\UnitMovement $unitMovement
     * @return \Entity\Mission
     */
    public function setUnitMovement(UnitMovement $unitMovement = null)
    {
        $this->unitMovement = $unitMovement;

        return $this;
    }

    /**
     * Get UnitMovement entity (many to one).
     *
     * @return \Entity\UnitMovement
     */
    public function getUnitMovement()
    {
        return $this->unitMovement;
    }
}