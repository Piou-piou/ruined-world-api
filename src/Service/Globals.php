<?php

namespace App\Service;

use App\Entity\Base;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
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
	 * @var Request|null
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
	 * @return bool
	 */
	private function setBaseAndToken(): bool
	{
		$infos = null;
		$user = null;
		
		if ($this->session->has("jwt_infos") && $this->session->has("user")) {
			$infos = $this->session->get("jwt_infos");
			$user = $this->session->get("user");
		}
		
		if ($user !== null && $infos !== null && $infos->guid_base !== null) {
			$current_base = $this->em->getRepository(Base::class)->findOneBy([
				"guid" => $infos->guid_base,
				"user" => $user,
			]);
			$this->em->refresh($current_base);
			$this->session->set("current_base", $current_base);
			$this->session->set("token", $user->getToken());
			
			return true;
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
		if ($this->setBaseAndToken() === false) {
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
	
	/**
	 * method that return the array of the construction's coefs json file
	 * @return mixed
	 */
	public function getCoefForConstruction()
	{
		$coef_construction = json_decode(file_get_contents($this->container->getParameter("game_data_directory") . "coef_for_construction.json"), true);
		
		return $coef_construction;
	}
	
	/**
	 * method that return the array of the production's coefs json file
	 * @return mixed
	 */
	public function getCoefForProduction()
	{
		$coef_production = json_decode(file_get_contents($this->container->getParameter("game_data_directory") . "coef_for_production.json"), true);

		return $coef_production;
	}
	
	/**
	 * method that return the array of the storage's coefs json file
	 * @return mixed
	 */
	public function getCoefForStorage()
	{
		$coef_storage = json_decode(file_get_contents($this->container->getParameter("game_data_directory") . "coef_for_storage.json"), true);
		
		return $coef_storage;
	}
	
	/**
	 * method that return the array of the points to win/loose based on a given name
	 * @return mixed
	 */
	public function getPointsConfig()
	{
		$points = json_decode(file_get_contents($this->container->getParameter("game_data_directory") . "points.json"), true);
		
		return $points;
	}
}