<?php

namespace App\Service;

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

	public function __construct(EntityManagerInterface $em, SessionInterface $session, Globals $globals)
	{
		$this->em = $em;
		$this->session = $session;
		$this->globals = $globals;
	}

	/**
	 * method that return number of food consumed per hour
	 * @return int
	 * @throws NonUniqueResultException
	 */
	public function getFoodConsumedPerHour(): int
	{
		$units_number = $this->em->getRepository(Unit::class)->countUnitsInBase($this->globals->getCurrentBase());

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
		$base = $this->globals->getCurrentBase(true);

		if ($last_hour >= $base->getLastCheckFood()) {
			$new_food = $base->getFood() - $this->getFoodConsumedPerHour();

			if ($new_food < 0) {
				$this->killUnits($new_food);
				$new_food = 0;
			}

			$base->setFood($new_food);
			$base->setLastCheckFood(new DateTime());
			$this->em->persist($base);
			$this->em->flush();
		}
	}

	private function killUnits($negative_food) {

	}
}