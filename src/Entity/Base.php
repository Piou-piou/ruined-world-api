<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * Entity\Base
 *
 * @ORM\Entity(repositoryClass="BaseRepository")
 * @ORM\Table(name="base", indexes={@ORM\Index(name="fk_base_user_idx", columns={"user_id"})})
 */
class Base
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\Column(name="`name`", type="string", length=20)
     */
    protected $name;

    /**
     * @ORM\Column(type="integer")
     */
    protected $points;

    /**
     * @ORM\Column(type="text")
     */
    protected $resources;

    /**
     * @ORM\Column(type="integer")
     */
    protected $posx;

    /**
     * @ORM\Column(type="integer")
     */
    protected $posy;

    /**
     * @ORM\Column(type="datetime")
     */
    protected $last_update_resources;

    /**
     * @ORM\Column(type="datetime")
     */
    protected $last_check_mission;

    /**
     * @ORM\Column(type="datetime")
     */
    protected $last_check_food;

    /**
     * @ORM\Column(type="integer")
     */
    protected $user_id;

    /**
     * @ORM\OneToMany(targetEntity="Building", mappedBy="base")
     * @ORM\JoinColumn(name="id", referencedColumnName="base_id", nullable=false)
     */
    protected $buildings;

    /**
     * @ORM\OneToMany(targetEntity="Mission", mappedBy="base")
     * @ORM\JoinColumn(name="id", referencedColumnName="base_id", nullable=false)
     */
    protected $missions;

    /**
     * @ORM\OneToMany(targetEntity="Unit", mappedBy="base")
     * @ORM\JoinColumn(name="id", referencedColumnName="base_id", nullable=false)
     */
    protected $units;

    /**
     * @ORM\OneToMany(targetEntity="UnitMovement", mappedBy="base")
     * @ORM\JoinColumn(name="id", referencedColumnName="base_id", nullable=false)
     */
    protected $unitMovements;

    /**
     * @ORM\ManyToOne(targetEntity="User", inversedBy="bases")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id", nullable=false)
     */
    protected $user;

    public function __construct()
    {
        $this->buildings = new ArrayCollection();
        $this->missions = new ArrayCollection();
        $this->units = new ArrayCollection();
        $this->unitMovements = new ArrayCollection();
    }

    /**
     * Set the value of id.
     *
     * @param integer $id
     * @return \Entity\Base
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
     * @return \Entity\Base
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
     * Set the value of points.
     *
     * @param integer $points
     * @return \Entity\Base
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
     * Set the value of resources.
     *
     * @param string $resources
     * @return \Entity\Base
     */
    public function setResources($resources)
    {
        $this->resources = $resources;

        return $this;
    }

    /**
     * Get the value of resources.
     *
     * @return string
     */
    public function getResources()
    {
        return $this->resources;
    }

    /**
     * Set the value of posx.
     *
     * @param integer $posx
     * @return \Entity\Base
     */
    public function setPosx($posx)
    {
        $this->posx = $posx;

        return $this;
    }

    /**
     * Get the value of posx.
     *
     * @return integer
     */
    public function getPosx()
    {
        return $this->posx;
    }

    /**
     * Set the value of posy.
     *
     * @param integer $posy
     * @return \Entity\Base
     */
    public function setPosy($posy)
    {
        $this->posy = $posy;

        return $this;
    }

    /**
     * Get the value of posy.
     *
     * @return integer
     */
    public function getPosy()
    {
        return $this->posy;
    }

    /**
     * Set the value of last_update_resources.
     *
     * @param \DateTime $last_update_resources
     * @return \Entity\Base
     */
    public function setLastUpdateResources($last_update_resources)
    {
        $this->last_update_resources = $last_update_resources;

        return $this;
    }

    /**
     * Get the value of last_update_resources.
     *
     * @return \DateTime
     */
    public function getLastUpdateResources()
    {
        return $this->last_update_resources;
    }

    /**
     * Set the value of last_check_mission.
     *
     * @param \DateTime $last_check_mission
     * @return \Entity\Base
     */
    public function setLastCheckMission($last_check_mission)
    {
        $this->last_check_mission = $last_check_mission;

        return $this;
    }

    /**
     * Get the value of last_check_mission.
     *
     * @return \DateTime
     */
    public function getLastCheckMission()
    {
        return $this->last_check_mission;
    }

    /**
     * Set the value of last_check_food.
     *
     * @param \DateTime $last_check_food
     * @return \Entity\Base
     */
    public function setLastCheckFood($last_check_food)
    {
        $this->last_check_food = $last_check_food;

        return $this;
    }

    /**
     * Get the value of last_check_food.
     *
     * @return \DateTime
     */
    public function getLastCheckFood()
    {
        return $this->last_check_food;
    }

    /**
     * Set the value of user_id.
     *
     * @param integer $user_id
     * @return \Entity\Base
     */
    public function setUserId($user_id)
    {
        $this->user_id = $user_id;

        return $this;
    }

    /**
     * Get the value of user_id.
     *
     * @return integer
     */
    public function getUserId()
    {
        return $this->user_id;
    }

    /**
     * Add Building entity to collection (one to many).
     *
     * @param \Entity\Building $building
     * @return \Entity\Base
     */
    public function addBuilding(Building $building)
    {
        $this->buildings[] = $building;

        return $this;
    }

    /**
     * Remove Building entity from collection (one to many).
     *
     * @param \Entity\Building $building
     * @return \Entity\Base
     */
    public function removeBuilding(Building $building)
    {
        $this->buildings->removeElement($building);

        return $this;
    }

    /**
     * Get Building entity collection (one to many).
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getBuildings()
    {
        return $this->buildings;
    }

    /**
     * Add Mission entity to collection (one to many).
     *
     * @param \Entity\Mission $mission
     * @return \Entity\Base
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
     * @return \Entity\Base
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
     * @return \Entity\Base
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
     * @return \Entity\Base
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
     * Add UnitMovement entity to collection (one to many).
     *
     * @param \Entity\UnitMovement $unitMovement
     * @return \Entity\Base
     */
    public function addUnitMovement(UnitMovement $unitMovement)
    {
        $this->unitMovements[] = $unitMovement;

        return $this;
    }

    /**
     * Remove UnitMovement entity from collection (one to many).
     *
     * @param \Entity\UnitMovement $unitMovement
     * @return \Entity\Base
     */
    public function removeUnitMovement(UnitMovement $unitMovement)
    {
        $this->unitMovements->removeElement($unitMovement);

        return $this;
    }

    /**
     * Get UnitMovement entity collection (one to many).
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getUnitMovements()
    {
        return $this->unitMovements;
    }

    /**
     * Set User entity (many to one).
     *
     * @param \Entity\User $user
     * @return \Entity\Base
     */
    public function setUser(User $user = null)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Get User entity (many to one).
     *
     * @return \Entity\User
     */
    public function getUser()
    {
        return $this->user;
    }
}