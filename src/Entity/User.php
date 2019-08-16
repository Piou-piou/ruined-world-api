<?php

namespace App\Entity;

use DateTime;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * Entity\User
 *
 * @ORM\Entity(repositoryClass="App\Repository\UserRepository")
 * @ORM\Table(name="`user`")
 */
class User implements UserInterface
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
	 * @Groups("main")
     */
    protected $id;

    /**
     * @ORM\Column(type="string", length=50)
	 * @Groups("main")
     */
    protected $pseudo;
	
	/**
	 * @ORM\Column(type="string", length=255)
	 */
    protected $mail;
	
	/**
	 * @ORM\Column(type="string", length=200)
	 * @Groups("main")
	 */
    protected $token;
	
	/**
	 * @ORM\Column(type="datetime", nullable=true)
	 */
	private $endToken;

    /**
     * @ORM\Column(name="`password`", type="string", length=255)
     */
    protected $password;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    protected $plain_password;

    /**
     * @ORM\Column(type="integer")
	 * @Groups("main")
     */
    protected $points;

	/**
	 * @ORM\Column(type="json", nullable=true)
	 * @Groups("main")
	 */
    protected $premium_advantages;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    protected $last_connection;
	
	/**
	 * @ORM\Column(type="boolean", options={"default" : 0})
	 * @Groups("main")
	 */
	protected $holidays = false;
	
	/**
	 * @ORM\Column(type="boolean", options={"default" : 0})
	 * @Groups("main")
	 */
	protected $archived = false;
	
	/**
	 * @ORM\Column(type="boolean", options={"default" : 0})
	 */
	protected $verified_account =false;
	
	/**
	 * @ORM\Column(type="string", length=255, nullable=true)
	 */
	protected $validate_account_key;

	/**
	 * @ORM\Column(type="datetime", nullable=false)
	 */
	protected $created_at;

    /**
     * @ORM\OneToMany(targetEntity="Base", mappedBy="user")
     * @ORM\JoinColumn(name="id", referencedColumnName="user_id", nullable=false)
     */
    protected $bases;

	/**
	 * @ORM\OneToMany(targetEntity="MessageBox", mappedBy="user")
	 * @ORM\JoinColumn(name="id", referencedColumnName="user_id", nullable=true)
	 */
	protected $messages_box;

	/**
	 * @ORM\OneToMany(targetEntity="Message", mappedBy="user")
	 * @ORM\JoinColumn(name="id", referencedColumnName="user_id", nullable=true)
	 */
	protected $sent_messages;

	/**
	 * @ORM\OneToMany(targetEntity="UserToken", mappedBy="user")
	 * @ORM\JoinColumn(name="id", referencedColumnName="user_id", nullable=false)
	 */
	protected $tokens;

    public function __construct()
    {
        $this->bases = new ArrayCollection();
        $this->messages_box = new ArrayCollection();
        $this->sent_messages = new ArrayCollection();
    }
	
	/**
	 * Set the value of id.
	 *
	 * @param integer $id
	 * @return User
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
	 * @return User
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
	 * @return mixed
	 */
	public function getMail()
	{
		return $this->mail;
	}

	/**
	 * @param $mail
	 * @return $this
	 */
	public function setMail($mail)
	{
		$this->mail = $mail;

		return $this;
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
	 * @return User
	 */
	public function setToken($token)
	{
		$this->token = $token;
		
		return $this;
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
	 * @return User
	 */
	public function setEndToken($endToken)
	{
		$this->endToken = $endToken;
		
		return $this;
	}
	
	/**
	 * Set the value of password.
	 *
	 * @param string $password
	 * @return User
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
	 * @return User
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
	 * @return User
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
	 * @return mixed
	 */
	public function getPremiumAdvantages()
	{
		return $this->premium_advantages;
	}

	/**
	 * @param mixed $premium_advantages
	 * @return User
	 */
	public function setPremiumAdvantages($premium_advantages)
	{
		$this->premium_advantages = $premium_advantages;

		return $this;
	}
	
	/**
	 * Set the value of last_connection.
	 *
	 * @param \DateTime $last_connection
	 * @return User
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
	 * @return mixed
	 */
	public function getHolidays()
	{
		return $this->holidays;
	}
	
	/**
	 * @param mixed $holidays
	 * @return User
	 */
	public function setHolidays($holidays)
	{
		$this->holidays = $holidays;
		
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
	 * @return User
	 */
	public function setArchived($archived)
	{
		$this->archived = $archived;
		
		return $this;
	}
	
	/**
	 * @return mixed
	 */
	public function getVerifiedAccount()
	{
		return $this->verified_account;
	}
	
	/**
	 * @param mixed $verified_account
	 */
	public function setVerifiedAccount($verified_account): void
	{
		$this->verified_account = $verified_account;
	}
	
	/**
	 * @return mixed
	 */
	public function getValidateAccountKey()
	{
		return $this->validate_account_key;
	}
	
	/**
	 * @param mixed $validate_account_key
	 */
	public function setValidateAccountKey($validate_account_key): void
	{
		$this->validate_account_key = $validate_account_key;
	}
	
	/**
	 * Add Base entity to collection (one to many).
	 *
	 * @param Base $base
	 * @return User
	 */
    public function addBase(Base $base)
    {
        $this->bases[] = $base;

        return $this;
    }
	
	/**
	 * Remove Base entity from collection (one to many).
	 *
	 * @param Base $base
	 * @return User
	 */
    public function removeBase(Base $base)
    {
        $this->bases->removeElement($base);

        return $this;
    }

    /**
     * Get Base entity collection (one to many).
     *
     * @return Collection
     */
    public function getBases()
    {
        return $this->bases;
    }

	/**
	 * Add MessageBox entity to collection (one to many).
	 *
	 * @param MessageBox $message
	 * @return User
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
	 * @return User
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

	/**
	 * Add MessageBox entity to collection (one to many).
	 *
	 * @param Message $message
	 * @return User
	 */
	public function addSentMessages(Message $message)
	{
		$this->sent_messages[] = $message;

		return $this;
	}

	/**
	 * Remove MessageBox entity from collection (one to many).
	 *
	 * @param Message $message
	 * @return User
	 */
	public function removeSentMessages(Message $message)
	{
		$this->sent_messages->removeElement($message);

		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getSentMessages()
	{
		return $this->sent_messages;
	}

	/**
	 * Add Base entity to collection (one to many).
	 *
	 * @param UserToken $user_token
	 * @return User
	 */
	public function addToken(UserToken $user_token)
	{
		$this->tokens[] = $user_token;

		return $this;
	}

	/**
	 * Remove Base entity from collection (one to many).
	 *
	 * @param UserToken $user_token
	 * @return User
	 */
	public function removeToken(UserToken $user_token)
	{
		$this->tokens->removeElement($user_token);

		return $this;
	}

	/**
	 * Get Base entity collection (one to many).
	 *
	 * @return Collection
	 */
	public function getTokens()
	{
		return $this->tokens;
	}

	/**
	 * @return mixed
	 */
	public function getCreatedAt()
	{
		return $this->created_at;
	}

	/**
	 * @param mixed $created_at
	 */
	public function setCreatedAt($created_at): void
	{
		$this->created_at = $created_at;
	}

	/**
	 * @ORM\PrePersist
	 * @ORM\PreUpdate
	 */
	public function updatedTimestamps(): void
	{
		$now = new DateTime();
		if ($this->getCreatedAt() === null) {
			$this->setCreatedAt($now);
		}
	}

	/**
	 * method to get bases number for a user
	 * @Groups("main")
	 * @return int
	 */
	public function getBasesNumber(): int
	{
		return $this->bases->count();
	}

	/**
	 * method to test if user has premium waiting line
	 * @return bool
	 */
	public function hasPremiumWaitingLine(): bool
	{
		if (is_array($this->premium_advantages) && array_key_exists("wainting_line", $this->premium_advantages)) {
			return true;
		}

		return false;
	}

	/**
	 * method to test if user has premium storage
	 * @return bool
	 */
	public function hasPremiumFullStorage(): bool
	{
		if (is_array($this->premium_advantages) && array_key_exists("full_storage", $this->premium_advantages)) {
			return true;
		}

		return false;
	}

	/**
	 * Returns the roles granted to the user.
	 *
	 *     public function getRoles()
	 *     {
	 *         return ['ROLE_USER'];
	 *     }
	 *
	 * Alternatively, the roles might be stored on a ``roles`` property,
	 * and populated in any number of different ways when the user object
	 * is created.
	 *
	 * @return (Role|string)[] The user roles
	 */
	public function getRoles()
	{
		return [];
	}

	/**
	 * Returns the salt that was originally used to encode the password.
	 *
	 * This can return null if the password was not encoded using a salt.
	 *
	 * @return string|null The salt
	 */
	public function getSalt()
	{
		return '';
	}

	/**
	 * Returns the username used to authenticate the user.
	 *
	 * @return string The username
	 */
	public function getUsername()
	{
		return $this->getPseudo();
	}

	/**
	 * Removes sensitive data from the user.
	 *
	 * This is important if, at any given point, sensitive information like
	 * the plain-text password is stored on this object.
	 */
	public function eraseCredentials()
	{
		return null;
	}
}