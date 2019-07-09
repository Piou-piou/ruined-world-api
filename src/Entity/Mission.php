<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Entity\Mission
 *
 * @ORM\Entity
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
     * @ORM\Column(type="boolean")
     */
    protected $in_progress;

	/**
	 * @ORM\Column(type="integer")
	 */
    protected $missions_config_id;

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
     * @return Mission
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
     * Set the value of in_progress.
     *
     * @param boolean $in_progress
     * @return Mission
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
	 * @return mixed
	 */
	public function getMissionsConfigId()
	{
		return $this->missions_config_id;
	}

	/**
	 * @param mixed $missions_config_id
	 * @return Mission
	 */
	public function setMissionsConfigId($missions_config_id)
	{
		$this->missions_config_id = $missions_config_id;

		return $this;
	}

    /**
     * Set Base entity (many to one).
     *
     * @param Base $base
     * @return Mission
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
     * @return Mission
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