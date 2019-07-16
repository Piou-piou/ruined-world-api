<?php

namespace App\Service;

use Doctrine\ORM\EntityManagerInterface;

class Mission
{
	/**
	 * @var EntityManagerInterface
	 */
	private $em;

	/**
	 * @var Globals
	 */
	private $globals;

	private $user_number_mission= [];

	/**
	 * Mission constructor.
	 * @param EntityManagerInterface $em
	 * @param Globals $globals
	 */
	public function __construct(EntityManagerInterface $em, Globals $globals)
	{
		$this->em = $em;
		$this->globals = $globals;
	}

	/**
	 * method that get a number of missions that is not used yet
	 * @param int $max_number
	 * @return int
	 */
	private function getAleatoryNumber(int $max_number): int
	{
		$number = rand(1, $max_number);

		if (in_array($number, $this->user_number_mission)) {
			return $this->getAleatoryNumber($max_number);
		} else {
			array_push($this->user_number_mission, $number);
			return $number;
		}
	}

	/**
	 * method use to define aleatory missions for the base
	 */
	public function setAleatoryMissionsForBase()
	{
		$this->deleteMissionsOfBase();
		$this->user_number_mission = [];
		$missions_config = $this->globals->getMissionsConfig();
		$number_missions = $this->globals->getGeneralConfig()["number_of_missions_base"];

		for ($i = 0; $i < $number_missions; $i++) {
			$mission_id = $this->getAleatoryNumber(count($missions_config));

			$mission = new \App\Entity\Mission();
			$mission->setMissionsConfigId($mission_id);
			$mission->setBase($this->globals->getCurrentBase());
			$this->em->persist($mission);
		}

		$this->globals->getCurrentBase()->setLastCheckMission(new \DateTime());

		$this->em->flush();
	}

	/**
	 * remove missions that are not in movement now else hide them
	 * if mission is in progress then we disable it to not send units on it anymore
	 */
	public function deleteMissionsOfBase()
	{
		$missions = $this->em->getRepository(\App\Entity\Mission::class)->findBy([
			"base" => $this->globals->getCurrentBase(true),
		]);

		foreach ($missions as $mission) {
			if (!$mission->getInProgress()) {
				$this->em->remove($mission);
			} else {
				$mission->setDisabled(true);
				$this->em->persist($mission);
				$this->em->flush();
			}
		}
	}
}