<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * App\Entity\MarketMovement
 *
 * @ORM\Entity(repositoryClass="App\Repository\MarketMovement")
 * @ORM\Table(name="market_movement", indexes={@ORM\Index(name="fk_market_movement_base1_idx", columns={"base_id"})})
 */
class MarketMovement
{
	const TYPE_GO = 0,
		TYPE_RETURN = 1;

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\Column(type="datetime", nullable=false)
     */
    protected $end_date;

    /**
     * @ORM\Column(type="integer", nullable=false)
     */
    protected $duration;

    /**
     * @ORM\Column(name="`type`", type="integer", nullable=false)
     */
    protected $type;

    /**
     * @ORM\Column(type="json")
     */
    protected $resources;

    /**
     * @ORM\Column(type="integer")
     */
    protected $trader_number;

    /**
     * @ORM\ManyToOne(targetEntity="Base", inversedBy="marketMovements")
     * @ORM\JoinColumn(name="base_id", referencedColumnName="id", nullable=false)
     */
    protected $base;

	/**
	 * @ORM\ManyToOne(targetEntity="Base", inversedBy="marketMovementsDest")
	 * @ORM\JoinColumn(name="base_id_dest", referencedColumnName="id", nullable=false)
	 */
	protected $baseDest;

    /**
     * Set the value of id.
     *
     * @param integer $id
     * @return MarketMovement
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
     * @return MarketMovement
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
     * Set the value of duration.
     *
     * @param integer $duration
     * @return MarketMovement
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
     * Set the value of type.
     *
     * @param integer $type
     * @return MarketMovement
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
    public function getResources()
    {
        return $this->resources;
    }

    /**
     * @param mixed $resources
     * @return MarketMovement
     */
    public function setResources($resources)
    {
        $this->resources = $resources;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getTraderNumber()
    {
        return $this->trader_number;
    }

    /**
     * @param mixed $trader_number
     * @return MarketMovement
     */
    public function setTraderNumber($trader_number)
    {
        $this->trader_number = $trader_number;

        return $this;
    }

    /**
     * Set Base entity (many to one).
     *
     * @param Base $base
     * @return MarketMovement
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
	 * @return mixed
	 */
	public function getBaseDest()
	{
		return $this->baseDest;
	}

	/**
	 * @param mixed $baseDest
	 * @return MarketMovement
	 */
	public function setBaseDest($baseDest)
	{
		$this->baseDest = $baseDest;

		return $this;
	}
}