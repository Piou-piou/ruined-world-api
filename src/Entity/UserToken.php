<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Entity\UserToken
 *
 * @ORM\Entity
 * @ORM\Table(name="`user_token`",
 *     indexes = {
 *          @ORM\Index(name="fk_user_token_user_idx", columns={"user_id"})
 *     })
 */
class UserToken
{
	/**
	 * @ORM\Id
	 * @ORM\Column(type="integer")
	 * @ORM\GeneratedValue(strategy="AUTO")
	 */
	protected $id;

	/**
	 * @ORM\Column(type="string", length=200)
	 */
	protected $token;

	/**
	 * @ORM\Column(type="datetime", nullable=true)
	 */
	protected $endToken;

	/**
	 * @ORM\Column(type="string", length=200)
	 */
	protected $userAgent;

	/**
	 * @ORM\ManyToOne(targetEntity="User", inversedBy="tokens")
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
	 */
	public function setId($id): void
	{
		$this->id = $id;
	}

	/**
	 * @return mixed
	 */
	public function getToken()
	{
		return $this->token;
	}

	/**
	 * @param mixed $token
	 */
	public function setToken($token): void
	{
		$this->token = $token;
	}

	/**
	 * @return mixed
	 */
	public function getEndToken()
	{
		return $this->endToken;
	}

	/**
	 * @param mixed $endToken
	 */
	public function setEndToken($endToken): void
	{
		$this->endToken = $endToken;
	}

	/**
	 * @return mixed
	 */
	public function getUserAgent()
	{
		return $this->userAgent;
	}

	/**
	 * @param mixed $userAgent
	 */
	public function setUserAgent($userAgent): void
	{
		$this->userAgent = $userAgent;
	}

	/**
	 * Set User entity (many to one).
	 *
	 * @param User $user
	 * @return UserToken
	 */
	public function setUser(User $user = null)
	{
		$this->user = $user;

		return $this;
	}

	/**
	 * Get User entity (many to one).
	 *
	 * @return User
	 */
	public function getUser()
	{
		return $this->user;
	}
}