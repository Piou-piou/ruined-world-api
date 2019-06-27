<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * App\Entity\MarketDemand
 *
 * @ORM\Entity(repositoryClass="App\Repository\MarketMDemandRepository")
 * @ORM\Table(name="market_demand", indexes={@ORM\Index(name="fk_market_demand_base1_idx", columns={"base_id"})})
 */
class MarketDemand
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\Column(type="integer", nullable=false)
     */
    protected $ask;

    /**
     * @ORM\Column(type="string", length=100, nullable=false)
     */
    protected $asked_resource;

	/**
	 * @ORM\Column(type="integer", nullable=false)
	 */
	protected $trader_number_asked;

    /**
     * @ORM\Column(type="integer", nullable=false)
     */
    protected $offer;

    /**
     * @ORM\Column(type="string", length=100, nullable=false)
     */
    protected $offer_resource;

	/**
	 * @ORM\Column(type="integer", nullable=false)
	 */
    protected $trader_number_offer;

    /**
     * @ORM\Column(type="datetime", nullable=false)
     */
    protected $expiration_date;

    /**
     * @ORM\ManyToOne(targetEntity="Base", inversedBy="buildings")
     * @ORM\JoinColumn(name="base_id", referencedColumnName="id", nullable=false)
     */
    protected $base;

    /**
     * Set the value of id.
     *
     * @param integer $id
     * @return MarketDemand
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
     * Set the value of ask.
     *
     * @param integer $ask
     * @return MarketDemand
     */
    public function setAsk($ask)
    {
        $this->ask = $ask;

        return $this;
    }

    /**
     * Get the value of ask.
     *
     * @return integer
     */
    public function getAsk()
    {
        return $this->ask;
    }

    /**
     * Set the value of asked_resource.
     *
     * @param string $asked_resource
     * @return MarketDemand
     */
    public function setAskedResource($asked_resource)
    {
        $this->asked_resource = $asked_resource;

        return $this;
    }

    /**
     * Get the value of asked_resource.
     *
     * @return string
     */
    public function getAskedResource()
    {
        return $this->asked_resource;
    }

	/**
	 * @return mixed
	 */
	public function getTraderNumberAsked()
	{
		return $this->trader_number_asked;
	}

	/**
	 * @param mixed $trader_number_asked
	 * @return MarketDemand
	 */
	public function setTraderNumberAsked($trader_number_asked)
	{
		$this->trader_number_asked = $trader_number_asked;

		return $this;
	}

    /**
     * Set the value of offer.
     *
     * @param integer $offer
     * @return MarketDemand
     */
    public function setOffer($offer)
    {
        $this->offer = $offer;

        return $this;
    }

    /**
     * Get the value of offer.
     *
     * @return integer
     */
    public function getOffer()
    {
        return $this->offer;
    }

    /**
     * Set the value of offer_resource.
     *
     * @param string $offer_resource
     * @return MarketDemand
     */
    public function setOfferResource($offer_resource)
    {
        $this->offer_resource = $offer_resource;

        return $this;
    }

    /**
     * Get the value of offer_resource.
     *
     * @return string
     */
    public function getOfferResource()
    {
        return $this->offer_resource;
    }

	/**
	 * @return mixed
	 */
	public function getTraderNumberOffer()
	{
		return $this->trader_number_offer;
	}

	/**
	 * @param mixed $trader_number_offer
	 * @return MarketDemand
	 */
	public function setTraderNumberOffer($trader_number_offer)
	{
		$this->trader_number_offer = $trader_number_offer;

		return $this;
	}

    /**
     * Set the value of expiration_date.
     *
     * @param \DateTime $expiration_date
     * @return MarketDemand
     */
    public function setExpirationDate($expiration_date)
    {
        $this->expiration_date = $expiration_date;

        return $this;
    }

    /**
     * Get the value of expiration_date.
     *
     * @return \DateTime
     */
    public function getExpirationDate()
    {
        return $this->expiration_date;
    }

    /**
     * Set the value of base_id.
     *
     * @param integer $base_id
     * @return MarketDemand
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
     * Set Base entity (many to one).
     *
     * @param Base $base
     * @return MarketDemand
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
        return array('id', 'ask', 'asked_resource', 'offer', 'offer_resource', 'expiration_date', 'base_id');
    }
}