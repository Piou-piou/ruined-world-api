<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
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
	 * @ORM\OneToOne(targetEntity="User", inversedBy="league")
	 * @ORM\JoinColumn(name="leader_id", referencedColumnName="id", nullable=false)
	 * @Groups("main")
	 */
	protected $leader;

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
	public function getLeader()
	{
		return $this->leader;
	}

	/**
	 * @param mixed $leader
	 * @return League
	 */
	public function setLeader($leader): League
	{
		$this->leader = $leader;

		return $this;
	}
}