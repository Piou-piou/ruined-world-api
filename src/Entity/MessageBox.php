<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * Entity\Message
 *
 * @ORM\Entity
 * @ORM\Table(name="message_box")
 */
class MessageBox
{
	/**
	 * @ORM\Id
	 * @ORM\Column(type="integer")
	 * @ORM\GeneratedValue(strategy="AUTO")
	 */
	protected $id;

	/**
	 * @ORM\Column(name="`type`", type="integer")
	 */
	protected $type;

	/**
	 * @ORM\Column(type="datetime", nullable=true)
	 */
	protected $read_at;

	/**
	 * @ORM\ManyToOne(targetEntity="Message", inversedBy="messages_box")
	 * @ORM\JoinColumn(name="message_id", referencedColumnName="id", nullable=false)
	 */
	protected $message;

	/**
	 * @ORM\ManyToOne(targetEntity="User", inversedBy="messages_box")
	 * @ORM\JoinColumn(name="user_id", referencedColumnName="id", nullable=false)
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