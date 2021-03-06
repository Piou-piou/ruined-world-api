<?php

namespace App\Service;

use App\Entity\Base;
use App\Entity\Message;
use App\Entity\MessageBox;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Exception;

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

	/**
	 * @var Resources
	 */
	private $resources;

	/**
	 * @var Unit
	 */
	private $unit;

	private $user_number_mission= [];

	/**
	 * Mission constructor.
	 * @param EntityManagerInterface $em
	 * @param Globals $globals
	 * @param Resources $resources
	 */
	public function __construct(EntityManagerInterface $em, Globals $globals, Resources $resources, Unit $unit)
	{
		$this->em = $em;
		$this->globals = $globals;
		$this->resources = $resources;
		$this->unit = $unit;
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

	/**
	 * method called to end a mission kill unit based on lost percentage of it and give food based
	 * on win_resources percentage of mission
	 * @param Base $base
	 * @param \App\Entity\UnitMovement $unit_movement
	 * @param \App\Entity\Mission $mission
	 * @throws Exception
	 */
	public function endMission(Base $base, \App\Entity\UnitMovement $unit_movement, \App\Entity\Mission $mission)
	{
		$current_mission_config = $this->globals->getMissionsConfig()[$mission->getMissionsConfigId()];
		$units_sent = count($unit_movement->getUnits());
		$lost_unit = round($units_sent*(rand(0, $current_mission_config["lost_percentage"])/100));

		for ($i=0 ; $i<$lost_unit ; $i++) {
			$this->em->remove($unit_movement->getUnits()->get($i));
			$unit_movement->getUnits()->remove($i);
		}
		$this->em->persist($unit_movement);
		$this->em->flush();

		$max_transport_capacity = $this->unit->getMaxCapacityTransport($unit_movement->getUnits());
		$win_resources = round(($max_transport_capacity*((100+$current_mission_config["win_resources"])/100))-$max_transport_capacity);

		$this->resources->setBase($base);
		$this->resources->addResource("food", $win_resources);

		$mission->setInProgress(false);
		$mission->setUnitMovement(null);
		$this->em->persist($mission);
		$unit_movement->clearUnits();
		$this->em->persist($unit_movement);
		$this->em->flush();
		$this->em->remove($unit_movement);
		$this->em->flush();

		$this->setReportOfMission($unit_movement, $units_sent, $lost_unit, $win_resources);
	}

	/**
	 * method to create report of the mission
	 * @param \App\Entity\UnitMovement $unit_movement
	 * @param int $units_sent
	 * @param int $lost_unit
	 * @param int $win_resources
	 * @throws Exception
	 */
	private function setReportOfMission(\App\Entity\UnitMovement $unit_movement, int $units_sent, int $lost_unit, int $win_resources)
	{
		$text = "<h2>Rapport de mission</h2>";
		$text .= "Unités envoyées : ".$units_sent;
		$text .= "<br>Unités morte en mission : ".$lost_unit;
		$text .= "<h3>Ressources trouvées</h3>";
		$text .= "<ul>";
		$text .= "<li>Nourriture : " . $win_resources . "</li>";
		$text .= "</ul>";

		$message = new Message();
		$message->setSubject("Rapport de mission");
		$message->setMessage($text);
		$message->setSendAt(new DateTime());
		$message->setUser($this->globals->getWorldCenterUser());
		$this->em->persist($message);

		$message_box = new MessageBox();
		$message_box->setUser($unit_movement->getBase()->getUser());
		$message_box->setMessage($message);
		$message_box->setType(MessageBox::TYPE_OTHER);
		$message_box->setArchivedSent(true);
		$this->em->persist($message_box);

		$this->em->flush();
	}
}