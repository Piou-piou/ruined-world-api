<?php

namespace App\Service;

use App\Entity\Base;
use App\Entity\Unit;
use DateInterval;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\NonUniqueResultException;
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
	 * @throws NonUniqueResultException
	 */
	public function getFoodConsumedPerHour(): int
	{
		$units_number = $this->em->getRepository(Unit::class)->countUnitsInBase($this->base);

		return $units_number * 2;
	}

	/**
	 * methdo to consume food per hour
	 * @throws NonUniqueResultException
	 */
	public function consumeFood()
	{
		$last_hour = new DateTime();
		$last_hour->sub(new DateInterval("PT1H"));

		if ($this->base->getFood() > 0 && $last_hour > $this->base->getLastCheckFood()) {
			$new_food = $this->base->getFood() - $this->getFoodConsumedPerHour();

			if ($new_food < 0) {
				$this->killUnits($new_food);
				$new_food = 0;
			}

			$this->base->setFood($new_food);
			$this->base->setLastCheckFood(new DateTime());
			$this->em->persist($this->base);
			$this->em->flush();
		}
	}

	private function killUnits($negative_food) {

	}
}