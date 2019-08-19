<?php

namespace App\Service;

use App\Entity\Unit;
use DateInterval;
use DateTime;
use Doctrine\DBAL\DBALException;
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

		return $units_number * $this->globals->getGeneralConfig()["unit_food_consumption_hour"];
	}

	/**
	 * method that return killed unit per hour when food is equal or under 0
	 * @param int|null $negative_food
	 * @return int
	 * @throws NonUniqueResultException
	 */
	public function getUnitKilledPerHour(int $negative_food = null): int
	{
		if ($negative_food === null) {
			$negative_food = $this->getFoodConsumedPerHour();
		}

		return round(abs($negative_food) / $this->globals->getGeneralConfig()["food_kill_divider"]);
	}

	/**
	 * methdo to consume food per hour
	 * @throws NonUniqueResultException
	 * @throws DBALException
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

	/**
	 * method to kill random units because of no more food in base
	 * @param $negative_food
	 * @throws DBALException
	 * @throws NonUniqueResultException
	 */
	private function killUnits($negative_food)
	{
		if ($this->getUnitKilledPerHour($negative_food) > 0) {
			$this->em->getRepository(Unit::class)->killRandomUnitBecauseFood($this->globals->getCurrentBase(), $this->getUnitKilledPerHour($negative_food));
		}
	}

	/**
	 * method to get strings for front infos
	 * @return array
	 * @throws NonUniqueResultException
	 */
	public function getFoodStriingsInfo(): array
	{
		$food_consumption = $this->getFoodConsumedPerHour();
		$string = "consommé par heure";

		if ($this->globals->getCurrentBase()->getFood() === 0 && $food_consumption > 0) {
			$food_consumption = $this->getUnitKilledPerHour();
			$string = "mort par heure";
		}

		return [
			"food_consumption" => $food_consumption,
			"string" => $string
		];
	}

	/**
	 * method to get when garner will be empty for premium
	 * @return array
	 * @throws NonUniqueResultException
	 */
	public function getEmptyStorageInHour(): array
	{
		if (!$this->session->get("user")->hasPremiumFullStorage()) {
			return [];
		}

		return [
			"food" => round($this->globals->getCurrentBase()->getFood() / $this->getFoodConsumedPerHour(), 1),
		];
	}
}