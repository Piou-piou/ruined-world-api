<?php

namespace App\Service;

use App\Entity\Base;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class Resources
{
	/**
	 * @var EntityManagerInterface
	 */
	private $em;
	
	/**
	 * @var SessionInterface
	 */
	private $session;
	
	/**
	 * @var Base
	 */
	private $base;
	
	/**
	 * Resources constructor.
	 * @param EntityManagerInterface $em
	 * @param SessionInterface $session
	 * @param Globals $globals
	 */
	public function __construct(EntityManagerInterface $em, SessionInterface $session, Globals $globals)
	{
		$this->em = $em;
		$this->session = $session;
		$this->base = $globals->getCurrentBase(true);
	}
	
	/**
	 * method called to add resource
	 * @param string $resource
	 * @param int $value_to_add
	 */
	public function addResource(string $resource, int $value_to_add) {
		$getter = "get".ucfirst($resource);
		$setter = "set".ucfirst($resource);
		
		$this->base->$setter($this->base->$getter()+$value_to_add);
		$this->em->flush();
	}
	
	/**
	 * method called to withdraw resource
	 * @param string $resource
	 * @param int $value_to_add
	 */
	public function withdrawResource(string $resource, int $value_to_add) {
		$getter = "get".ucfirst($resource);
		$setter = "set".ucfirst($resource);
		
		$this->base->$setter($this->base->$getter()-$value_to_add);
		$this->em->flush();
	}
}