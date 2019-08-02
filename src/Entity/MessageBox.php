<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * Entity\Message
 *
 * @ORM\Entity(repositoryClass="App\Repository\MessageBoxRepository")
 * @ORM\Table(name="message_box")
 */
class MessageBox
{
	const TYPE_RECEIVED = 1,
		TYPE_SEND = 2,
		FIGHT_REPORT = 3,
		TYPE_OTHER = 4;

	/**
	 * @ORM\Id
	 * @ORM\Column(type="integer")
	 * @ORM\GeneratedValue(strategy="AUTO")
	 * @Groups("main")
	 */
	protected $id;

	/**
	 * @ORM\Column(name="`type`", type="integer")
	 * @Groups("main")
	 */
	protected $type;

	/**
	 * @ORM\Column(type="datetime", nullable=true)
	 * @Groups("main")
	 */
	protected $read_at;

	/**
	 * @ORM\Column(type="boolean", options={"default" : 0})
	 */
	protected $archived = 0;

	/**
	 * @ORM\Column(type="boolean", options={"default" : 0})
	 */
	protected $archived_sent = 0;

	/**
	 * @ORM\ManyToOne(targetEntity="Message", inversedBy="messages_box")
	 * @ORM\JoinColumn(name="message_id", referencedColumnName="id", nullable=false)
	 * @Groups("main")
	 */
	protected $message;

	/**
	 * @ORM\ManyToOne(targetEntity="User", inversedBy="messages_box")
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
	 * @return MessageBox
	 */
	public function setId($id)
	{
		$this->id = $id;

		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getType()
	{
		return $this->type;
	}

	/**
	 * @param mixed $type
	 * @return MessageBox
	 */
	public function setType($type)
	{
		$this->type = $type;

		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getReadAt()
	{
		return $this->read_at;
	}

	/**
	 * @param mixed $read_at
	 * @return MessageBox
	 */
	public function setReadAt($read_at)
	{
		$this->read_at = $read_at;

		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getArchived()
	{
		return $this->archived;
	}

	/**
	 * @param mixed $archived
	 * @return MessageBox
	 */
	public function setArchived($archived)
	{
		$this->archived = $archived;

		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getArchivedSent()
	{
		return $this->archived_sent;
	}

	/**
	 * @param mixed $archived_sent
	 * @return MessageBox
	 */
	public function setArchivedSent($archived_sent)
	{
		$this->archived_sent = $archived_sent;

		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getMessage()
	{
		return $this->message;
	}

	/**
	 * @param mixed $message
	 * @return MessageBox
	 */
	public function setMessage($message)
	{
		$this->message = $message;

		return $this;
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
	 * @return MessageBox
	 */
	public function setUser($user)
	{
		$this->user = $user;

		return $this;
	}
}