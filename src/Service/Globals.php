<?php

namespace App\Service;

use App\Entity\Base;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class Globals
{
	/**
	 * @var ContainerInterface
	 */
	private $container;
	
	/**
	 * @var EntityManagerInterface
	 */
	private $em;
	
	/**
	 * @var SessionInterface
	 */
	private $session;
	
	public function __construct(ContainerInterface $container, EntityManagerInterface $em, SessionInterface $session)
	{
		$this->container = $container;
		$this->em = $em;
		$this->session = $session;
	}
	
	/**
	 * method that return current base entity
	 * @param bool $force_refresh
	 * @return mixed|object
	 */
	public function getCurrentBase($force_refresh = false)
	{
		$user = $this->em->getRepository(User::class)->findOneBy(["token" => $this->session->get("token")]);
		
		if ($this->session->has("current_base") === true && $user !== null) {
			if ($force_refresh) {
				
				$current_base = $this->em->getRepository(Base::class)->find($this->session->get("current_base")->getId());
				$this->em->refresh($current_base);
				$this->session->set("current_base", $current_base);
			}
			
			$base = $this->session->get("current_base");
			$base = $this->em->merge($base);
			
			return $base;
		}
		
		return false;
	}
	
	/**
	 * method that return the array of the building's config json file
	 * @return mixed
	 */
	public function getBuildingsConfig() {
		$buildings = json_decode(file_get_contents($this->container->getParameter("game_data_directory")."buildings.json"), true);
		
		return $buildings;
	}
}