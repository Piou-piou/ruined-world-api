<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * Entity\Unit
 *
 * @ORM\Entity(repositoryClass="App\Repository\UnitRepository")
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
	 * @ORM\Column(type="integer", options={"default" : 100})
	 */
    protected $life = 100;

	/**
	 * @ORM\Column(type="boolean", options={"default" : 0})
	 */
    protected $in_recruitment;

	/**
	 * @ORM\Column(type="datetime", nullable=true)
	 */
	protected $end_recruitment;

    /**
     * @ORM\ManyToOne(targetEntity="UnitGroup", inversedBy="units")
     * @ORM\JoinColumn(name="group_id", referencedColumnName="id", nullable=true)
     */
    protected $unitGroup;

    /**
     * @ORM\ManyToOne(targetEntity="Base", inversedBy="units")
     * @ORM\JoinColumn(name="base_id", referencedColumnName="id", nullable=false)
     */
    protected $base;

    /**
     * @ORM\ManyToOne(targetEntity="UnitMovement", inversedBy="units")
     * @ORM\JoinColumn(name="unit_movement_id", referencedColumnName="id", nullable=true)
     */
    protected $unitMovement;

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
	 * @return mixed
	 */
	public function getLife()
	{
		return $this->life;
	}

	/**
	 * @param mixed $life
	 * @return Unit
	 */
	public function setLife($life)
	{
		$this->life = $life;

		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getInRecruitment()
	{
		return $this->in_recruitment;
	}

	/**
	 * @param mixed $in_recruitment
	 * @return Unit
	 */
	public function setInRecruitment($in_recruitment)
	{
		$this->in_recruitment = $in_recruitment;

		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getEndRecruitment()
	{
		return $this->end_recruitment;
	}

	/**
	 * @param mixed $end_recruitment
	 * @return Unit
	 */
	public function setEndRecruitment($end_recruitment)
	{
		$this->end_recruitment = $end_recruitment;

		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getUnitGroup()
	{
		return $this->unitGroup;
	}

	/**
	 * @param mixed $unitGroup
	 * @return Unit
	 */
	public function setUnitGroup($unitGroup)
	{
		$this->unitGroup = $unitGroup;

		return $this;
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