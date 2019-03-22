<?php

namespace App\Service;

use App\Entity\Base;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
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
	
	/**
	 * @var \Symfony\Component\HttpFoundation\Request|null
	 */
	private $request;
	
	public function __construct(ContainerInterface $container, EntityManagerInterface $em, SessionInterface $session, RequestStack $request_stack)
	{
		$this->container = $container;
		$this->em = $em;
		$this->session = $session;
		$this->request = $request_stack->getCurrentRequest();
	}
	
	/**
	 * method that set session for base and token base on request parameters
	 * if no parameters in request test if the sessions already exists.
	 * if nothing exist send false else true
	 * @param string|null $token
	 * @param string|null $infos
	 * @return bool
	 */
	private function setBaseAndToken(?string $token, ?string $infos): bool
	{
		if ($token !== null && $infos !== null) {
			$json = Jwt::decode($infos, $token);
			
			if ($json !== false) {
				$user = $this->em->getRepository(User::class)->findOneBy(["token" => $token]);
				
				if (!$user) {
					return false;
				}
				
				$current_base = $this->em->getRepository(Base::class)->findOneBy([
					"guid" => $json->guid_base,
					"user" => $user,
				]);
				$this->em->refresh($current_base);
				$this->session->set("current_base", $current_base);
				$this->session->set("token", $token);
				
				return true;
			}
			
			return false;
		} else if (!$this->session->has("current_base") && !$this->session->has("token")) {
			return false;
		}
		
		return true;
	}
	
	/**
	 * method that return current base entity
	 * @param bool $force_refresh
	 * @return mixed|Base
	 */
	public function getCurrentBase($force_refresh = false)
	{
		if ($this->setBaseAndToken($this->request->get("token"), $this->request->get("infos")) === false) {
			return false;
		}
		
		if ($this->session->has("current_base") === true && $this->session->has("token") === true) {
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
	public function getBuildingsConfig()
	{
		$buildings = json_decode(file_get_contents($this->container->getParameter("game_data_directory") . "buildings.json"), true);
		
		return $buildings;
	}
}