<?php

namespace App\Service;

use App\Entity\Message;
use App\Entity\MessageBox;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class FightReport
{
	private $attack_units;
	private $defend_units;

	private $end_attack_units;
	private $end_defend_units;

	private $em;
	private $session;

	public function __construct(EntityManagerInterface $em, SessionInterface $session)
	{
		$this->em = $em;
		$this->session = $session;
	}

	public function setStartAttackUnits($attack_units)
	{
		$start_attack = [];
		if (count($attack_units) > 0) {
			$start_attack = clone $attack_units;
		}

		$this->attack_units = $start_attack;
	}

	public function setStartDefendUnits($defend_units)
	{
		$start_defend = $defend_units;
		$this->defend_units = $start_defend;
	}

	public function setEndAttackUnits($attack_units)
	{
		$this->end_attack_units = $attack_units;
	}

	public function setEndDefendUnits($defend_units)
	{
		$this->end_defend_units = $defend_units;
	}

	private function getUnitsNumberSentAndReturned($type) {
		$var = $type === "attack" ? "attack_units" : "defend_units";
		$endvar = $type === "attack" ? "end_attack_units" : "end_defend_units";
		$units = [];

		foreach ($this->$var as $unit) {
			if (array_key_exists($unit->getArrayName(), $units)) {
				$units[$unit->getArrayName()]["number"]++;
			} else {
				$units[$unit->getArrayName()] = [
					"name" => $unit->getName(),
					"number" => 1,
					"return_number" => 0
				];
			}
		}
		foreach ($this->$endvar as $unit) {
			$units[$unit->getArrayName()]["return_number"]++;
		}

		return $units;
	}

	public function createReport(\App\Entity\UnitMovement $unitMovement)
	{
		$attack_units = $this->getUnitsNumberSentAndReturned("attack");
		$defend_units = $this->getUnitsNumberSentAndReturned("defend");

		$text = "<h2>rapport des unités envoyées</h2>";
		foreach ($attack_units as $attack_unit) {
			$text .= $attack_unit["name"] . " qui ont survécus  : " . $attack_unit["return_number"] . " / " . $attack_unit["number"] . "<br>";
		}

		$text .= "<h2>rapport des unités attaquées</h2>";
		foreach ($defend_units as $defend_unit) {
			$text .= $defend_unit["name"] . " tués  : " . $defend_unit["return_number"] . " / " . $defend_unit["number"] . "<br>";
		}

		$text .= "<h2>Ressources volées</h2>";
		$text .= "<ul>";
		$text .= "<li>Electricité : ". $unitMovement->getElectricity() ."</li>";
		$text .= "<li>Fer : ". $unitMovement->getIron() ."</li>";
		$text .= "<li>Fuel : ". $unitMovement->getFuel() ."</li>";
		$text .= "<li>Eau : ". $unitMovement->getWater() ."</li>";
		$text .= "</ul>";

		$message = new Message();
		$message->setSubject("rapport de combat");
		$message->setMessage($text);
		$message->setSendAt(new DateTime());
		$message->setUser($this->session->get("user"));
		$this->em->persist($message);

		$message_box = new MessageBox();
		$message_box->setUser($this->session->get("user"));
		$message_box->setMessage($message);
		$message_box->setType(MessageBox::FIGHT_REPORT);
		$this->em->persist($message_box);

		$this->em->flush();
	}
}