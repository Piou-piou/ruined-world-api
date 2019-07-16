<?php

namespace App\Service;

use App\Entity\Base;
use App\Entity\Unit;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class Food
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
	 * @var Globals
	 */
	private $globals;

	/**
	 * @var Base
	 */
	private $base;

	public function __construct(EntityManagerInterface $em, SessionInterface $session, Globals $globals)
	{
		$this->em = $em;
		$this->session = $session;
		$this->globals = $globals;
		$this->base = $globals->getCurrentBase(true);
	}

	/**
	 * method that return number of food consumed per hour
	 * @return int
	 */
	private function getFoodConsumedPerHour(): int
	{
		$units = $this->em->getRepository(Unit::class)->findByUnitsInBase($this->base);

		return count($units) * 2;
	}

	/**
	 * methdo to consume food per hour
	 */
	public function consumeFood()
	{
		if ($this->base->getFood() > 0) {
			$new_food = $this->base->getFood() - $this->getFoodConsumedPerHour();

			if ($new_food < 0) {
				$this->killUnits($new_food);
				$new_food = 0;
			}

			$this->base->setFood($new_food);
			$this->em->persist($this->base);
			$this->em->flush();
		}
	}

	private function killUnits($negative_food) {

	}
}