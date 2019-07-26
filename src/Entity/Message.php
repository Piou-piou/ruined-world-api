<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * Entity\Message
 *
 * @ORM\Entity
 * @ORM\Table(name="message")
 */
class Message
{
	/**
	 * @ORM\Id
	 * @ORM\Column(type="integer")
	 * @ORM\GeneratedValue(strategy="AUTO")
	 */
	protected $id;

	/**
	 * @ORM\Column(type="string", length=255)
	 */
	protected $subject;

	/**
	 * @ORM\Column(type="text")
	 */
	protected $message;

	/**
	 * @ORM\Column(type="datetime")
	 */
	protected $send_at;

	/**
	 * @ORM\OneToMany(targetEntity="MessageBox", mappedBy="message")
	 * @ORM\JoinColumn(name="id", referencedColumnName="message_id", nullable=true)
	 */
	protected $messages_box;

	public function __construct()
	{
		$this->messages_box = new ArrayCollection();
	}

	/**
	 * @return mixed
	 */
	public function getId()
	{
		return $this->id;
	}

	/**
	 * @param mixed $id
	 * @return Message
	 */
	public function setId($id)
	{
		$this->id = $id;

		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getSubject()
	{
		return $this->subject;
	}

	/**
	 * @param mixed $subject
	 * @return Message
	 */
	public function setSubject($subject)
	{
		$this->subject = $subject;

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
	 * @return Message
	 */
	public function setMessage($message)
	{
		$this->message = $message;

		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getSendAt()
	{
		return $this->send_at;
	}

	/**
	 * @param mixed $send_at
	 * @return Message
	 */
	public function setSendAt($send_at)
	{
		$this->send_at = $send_at;

		return $this;
	}

	/**
	 * Add MessageBox entity to collection (one to many).
	 *
	 * @param MessageBox $message
	 * @return Message
	 */
	public function addMessagesBox(MessageBox $message)
	{
		$this->messages_box[] = $message;

		return $this;
	}

	/**
	 * Remove MessageBox entity from collection (one to many).
	 *
	 * @param MessageBox $message
	 * @return Message
	 */
	public function removeMessagesBox(MessageBox $message)
	{
		$this->messages_box->removeElement($message);

		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getMessagesBox()
	{
		return $this->messages_box;
	}
}