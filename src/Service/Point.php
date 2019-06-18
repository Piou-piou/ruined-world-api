<?php

namespace App\Service;

use App\Entity\Base;
use Doctrine\ORM\EntityManagerInterface;

class Point
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
	 * @var Base
	 */
	private $base;
	
	/**
	 * @var mixed
	 */
	private $points;
	
	/**
	 * Point constructor.
	 * @param EntityManagerInterface $em
	 * @param Globals $globals
	 */
	public function __construct(EntityManagerInterface $em, Globals $globals)
	{
		$this->em = $em;
		$this->globals = $globals;
		$this->base = $globals->getCurrentBase(true);
		$this->points = $globals->getPointsConfig();
	}
	
	/**
	 * method to add points to the base
	 * @param string $type
	 */
	public function addPoints(string $type)
	{
		if (array_key_exists($type, $this->points)) {
			$this->base->setPoints($this->base->getPoints() + $this->points[$type]);
			$this->em->flush();
		}
	}
	
	/**
	 * method to remove points of the base
	 * @param string $type
	 */
	public function removePoints(string $type)
	{
		if (array_key_exists($type, $this->points)) {
			$points_to_remove = $this->base->getPoints() - $this->points[$type] < 0 ? 0 : $this->base->getPoints() - $this->points[$type];
			$this->base->setPoints($points_to_remove);
			$this->em->flush();
		}
	}
}