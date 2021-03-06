<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * Entity\Building
 *
 * @ORM\Entity(repositoryClass="App\Repository\BuildingRepository")
 * @ORM\Table(name="building", indexes={@ORM\Index(name="fk_building_base1_idx", columns={"base_id"})})
 */
class Building
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\Column(name="`name`", type="string", length=40)
	 * @Groups("main")
     */
    protected $name;

    /**
     * @ORM\Column(type="string", length=40)
	 * @Groups("main")
     */
    protected $array_name;

    /**
     * @ORM\Column(name="level", type="integer")
	 * @Groups("main")
     */
    protected $level = 0;

    /**
     * @ORM\Column(name="location", type="integer")
	 * @Groups("main")
     */
    protected $location;

    /**
     * @ORM\Column(type="boolean", options={"default" : 0})
	 * @Groups("main")
     */
    protected $in_construction = false;

	/**
	 * @ORM\Column(type="datetime", nullable=true)
	 * @Groups("main")
	 */
    protected $start_construction;

    /**
     * @ORM\Column(type="datetime", nullable=true)
	 * @Groups("main")
     */
    protected $end_construction;

    /**
     * @ORM\ManyToOne(targetEntity="Base", inversedBy="buildings")
     * @ORM\JoinColumn(name="base_id", referencedColumnName="id", nullable=false)
	 * @Groups("main")
     */
    protected $base;

    /**
     * Set the value of id.
     *
     * @param integer $id
     * @return Building
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
     * @return Building
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
     * @return Building
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
     * Set the value of level.
     *
     * @param integer $level
     * @return Building
     */
    public function setLevel($level)
    {
        $this->level = $level;

        return $this;
    }

    /**
     * Get the value of level.
     *
     * @return integer
     */
    public function getLevel()
    {
        return $this->level;
    }
	
	/**
	 * Set the value of case.
	 *
	 * @param $location
	 * @return Building
	 */
    public function setLocation($location)
    {
        $this->location = $location;

        return $this;
    }

    /**
     * Get the value of case.
     *
     * @return integer
     */
    public function getLocation()
    {
        return $this->location;
    }

    /**
     * Set the value of in_construction.
     *
     * @param boolean $in_construction
     * @return Building
     */
    public function setInConstruction($in_construction)
    {
        $this->in_construction = $in_construction;

        return $this;
    }

    /**
     * Get the value of in_construction.
     *
     * @return boolean
     */
    public function getInConstruction()
    {
        return $this->in_construction;
    }

	/**
	 * @return mixed
	 */
	public function getStartConstruction()
	{
		return $this->start_construction;
	}

	/**
	 * @param \DateTime $start_construction
	 * @return Building
	 */
	public function setStartConstruction($start_construction)
	{
		$this->start_construction = $start_construction;

		return $this;
	}

    /**
     * Set the value of end_construction.
     *
     * @param \DateTime $end_construction
     * @return Building
     */
    public function setEndConstruction($end_construction)
    {
        $this->end_construction = $end_construction;

        return $this;
    }

    /**
     * Get the value of end_construction.
     *
     * @return \DateTime
     */
    public function getEndConstruction()
    {
        return $this->end_construction;
    }

    /**
     * Set Base entity (many to one).
     *
     * @param Base $base
     * @return Building
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
}