<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * App\Entity\MarketMovement
 *
 * @ORM\Entity
 * @ORM\Table(name="market_movement", indexes={@ORM\Index(name="fk_market_movement_base1_idx", columns={"base_id"})})
 */
class MarketMovement
{
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
     * @ORM\Column(type="integer", nullable=false)
     */
    protected $base_id_dest;

    /**
     * @ORM\ManyToOne(targetEntity="Base", inversedBy="buildings")
     * @ORM\JoinColumn(name="base_id", referencedColumnName="id", nullable=false)
     */
    protected $base;

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
     * Set the value of base_id_dest.
     *
     * @param integer $base_id_dest
     * @return MarketMovement
     */
    public function setBaseIdDest($base_id_dest)
    {
        $this->base_id_dest = $base_id_dest;

        return $this;
    }

    /**
     * Get the value of base_id_dest.
     *
     * @return integer
     */
    public function getBaseIdDest()
    {
        return $this->base_id_dest;
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

    public function __sleep()
    {
        return array('id', 'end_date', 'duration', 'type', 'base_id_dest', 'base_id');
    }
}