<?php

namespace App\Service;

use App\Entity\Base;
use App\Entity\User;
use DateInterval;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
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

	/**
	 * @var User
	 */
	private $world_user;
	
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
			$this->session->set("token", $this->session->get("user_token")->getToken());
			
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
	 * method to get world center user
	 * @return User
	 */
	public function getWorldCenterUser(): User
	{
		if (!$this->world_user) {
			$this->world_user = $this->em->getRepository(User::class)->findOneBy(["pseudo" => "world-center"]);
		}

		return $this->world_user;
	}

	/**
	 * method that calcul the time to travel between to bases based on speed of unit
	 * @param Base $first_base
	 * @param Base $second_base
	 * @param int $speed
	 * @param bool $to_hms
	 * @return mixed
	 */
	public function getTimeToTravel(Base $first_base, Base $second_base, int $speed = 1, $to_hms = false)
	{
		$multiplicator_time = $this->getGeneralConfig()["multiplicator_travel_time"];
		$posx_calc = abs(($first_base->getPosx()-$second_base->getPosx())*$multiplicator_time);
		$posy_calc = abs(($first_base->getPosy()-$second_base->getPosy())*$multiplicator_time);
		$time = round(($posx_calc+$posy_calc)/$speed);

		if ($to_hms) {
			return Utils::secondsToHms($time);
		}

		return $time;
	}

	/**
	 * method that send true if we can attack a player
	 * @param User $user
	 * @return bool
	 * @throws Exception
	 */
	public function canAttackPlayer(User $user)
	{
		$created_at = $user->getCreatedAt();
		$now = new \DateTime();
		$protection_days = $this->getGeneralConfig()["beginner_fight_protection_days"];

		if ($user->getHolidays() === true) {
			return false;
		} else if ($now->sub(new DateInterval("P".$protection_days."D")) > $created_at) {
			return true;
		}

		return false;
	}

	/**
	 * method to get max building can be put in waiting to build
	 * @return mixed
	 */
	public function getMaxConstructionInConstructionWaiting()
	{
		$construction_length = $this->getUserNationConfig()["construction_line_length"];

		if ($this->session->get("user")->hasPremiumWaitingLine()) {
			$construction_length++;
		}

		return $construction_length;
	}

	/**
	 * method that return the array of the building's config json file
	 * @return mixed
	 */
	public function getGeneralConfig()
	{
		$general = json_decode(file_get_contents($this->container->getParameter("game_data_directory") . "general.json"), true);

		return $general;
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

	/**
	 * method that return the array of the units config json file
	 * @param string $type
	 * @return mixed
	 */
	public function getUnitsConfig(string $type = "all")
	{
		$units = [];
		$trucks = [];
		if ($type === "all") {
			$units = json_decode(file_get_contents($this->container->getParameter("game_data_directory") . "units.json"), true);
			$trucks = json_decode(file_get_contents($this->container->getParameter("game_data_directory") . "trucks.json"), true);
		} else if ($type === "units") {
			$units = json_decode(file_get_contents($this->container->getParameter("game_data_directory") . "units.json"), true);
		}

		return array_merge($units, $trucks);
	}

	/**
	 * method that return the array of the missions config json file
	 * @return mixed
	 */
	public function getMissionsConfig()
	{
		$missions = json_decode(file_get_contents($this->container->getParameter("game_data_directory") . "missions.json"), true);

		return $missions;
	}

	/**
	 * method that return the array of the premium config json file
	 * @return mixed
	 */
	public function getPremiumConfig()
	{
		$premium = json_decode(file_get_contents($this->container->getParameter("game_data_directory") . "premium.json"), true);

		return $premium;
	}

	/**
	 * method that return the array of the premium config json file
	 * @return mixed
	 */
	public function getNationsConfig()
	{
		$nations = json_decode(file_get_contents($this->container->getParameter("game_data_directory") . "nations.json"), true);

		return $nations;
	}

	/**
	 * method to get nation config for current user
	 * @return mixed
	 */
	public function getUserNationConfig()
	{
		return $this->getNationsConfig()[$this->session->get("user")->getNation()];
	}
}