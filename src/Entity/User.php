<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * Entity\User
 *
 * @ORM\Entity(repositoryClass="UserRepository")
 * @ORM\Table(name="`user`")
 */
class User
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\Column(type="string", length=50)
     */
    protected $pseudo;

    /**
     * @ORM\Column(name="`password`", type="string", length=255)
     */
    protected $password;

    /**
     * @ORM\Column(type="string", length=255)
     */
    protected $plain_password;

    /**
     * @ORM\Column(type="integer")
     */
    protected $points;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    protected $last_connection;

    /**
     * @ORM\OneToMany(targetEntity="Base", mappedBy="user")
     * @ORM\JoinColumn(name="id", referencedColumnName="user_id", nullable=false)
     */
    protected $bases;

    public function __construct()
    {
        $this->bases = new ArrayCollection();
    }

    /**
     * Set the value of id.
     *
     * @param integer $id
     * @return \Entity\User
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
     * Set the value of pseudo.
     *
     * @param string $pseudo
     * @return \Entity\User
     */
    public function setPseudo($pseudo)
    {
        $this->pseudo = $pseudo;

        return $this;
    }

    /**
     * Get the value of pseudo.
     *
     * @return string
     */
    public function getPseudo()
    {
        return $this->pseudo;
    }

    /**
     * Set the value of password.
     *
     * @param string $password
     * @return \Entity\User
     */
    public function setPassword($password)
    {
        $this->password = $password;

        return $this;
    }

    /**
     * Get the value of password.
     *
     * @return string
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * Set the value of plain_password.
     *
     * @param string $plain_password
     * @return \Entity\User
     */
    public function setPlainPassword($plain_password)
    {
        $this->plain_password = $plain_password;

        return $this;
    }

    /**
     * Get the value of plain_password.
     *
     * @return string
     */
    public function getPlainPassword()
    {
        return $this->plain_password;
    }

    /**
     * Set the value of points.
     *
     * @param integer $points
     * @return \Entity\User
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
     * Set the value of last_connection.
     *
     * @param \DateTime $last_connection
     * @return \Entity\User
     */
    public function setLastConnection($last_connection)
    {
        $this->last_connection = $last_connection;

        return $this;
    }

    /**
     * Get the value of last_connection.
     *
     * @return \DateTime
     */
    public function getLastConnection()
    {
        return $this->last_connection;
    }

    /**
     * Add Base entity to collection (one to many).
     *
     * @param \Entity\Base $base
     * @return \Entity\User
     */
    public function addBase(Base $base)
    {
        $this->bases[] = $base;

        return $this;
    }

    /**
     * Remove Base entity from collection (one to many).
     *
     * @param \Entity\Base $base
     * @return \Entity\User
     */
    public function removeBase(Base $base)
    {
        $this->bases->removeElement($base);

        return $this;
    }

    /**
     * Get Base entity collection (one to many).
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getBases()
    {
        return $this->bases;
    }
}