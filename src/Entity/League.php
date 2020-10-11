<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * Entity\League
 *
 * @ORM\Entity()
 * @ORM\Table(name="`league`")
 */
class League
{
	/**
	 * @ORM\Id
	 * @ORM\Column(type="integer")
	 * @ORM\GeneratedValue(strategy="AUTO")
	 * @Groups("main")
	 */
	protected $id;

	/**
	 * @ORM\Column(name="`name`", type="string", length=20)
	 * @Groups("main")
	 */
	protected $name;

	/**
	 * @ORM\Column(type="integer")
	 * @Groups("main")
	 */
	protected $points;

	/**
	 * @ORM\Column(type="boolean", options={"default" : 0})
	 */
	protected $archived = 0;

	/**
	 * @ORM\ManyToOne(targetEntity="User", inversedBy="leagues")
	 * @ORM\JoinColumn(name="user_id", referencedColumnName="id", nullable=false)
	 * @Groups("main")
	 */
	protected $user;

	/**
	 * @return mixed
	 */
	public function getId()
	{
		return $this->id;
	}

	/**
	 * @param mixed $id
	 */
	public function setId($id): void
	{
		$this->id = $id;
	}

	/**
	 * @return mixed
	 */
	public function getName()
	{
		return $this->name;
	}

	/**
	 * @param mixed $name
	 */
	public function setName($name): void
	{
		$this->name = $name;
	}

	/**
	 * @return mixed
	 */
	public function getPoints()
	{
		return $this->points;
	}

	/**
	 * @param mixed $points
	 */
	public function setPoints($points): void
	{
		$this->points = $points;
	}

	/**
	 * @return int
	 */
	public function getArchived(): int
	{
		return $this->archived;
	}

	/**
	 * @param int $archived
	 */
	public function setArchived(int $archived): void
	{
		$this->archived = $archived;
	}

	/**
	 * @return mixed
	 */
	public function getUser()
	{
		return $this->user;
	}

	/**
	 * @param mixed $user
	 * @return League
	 */
	public function setUser($user)
	{
		$this->user = $user;

		return $this;
	}
}