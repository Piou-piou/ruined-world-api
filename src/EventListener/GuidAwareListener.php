<?php

namespace App\EventListener;

use Doctrine\ORM\Event\LifecycleEventArgs;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\Exception\UnsatisfiedDependencyException;

class GuidAwareListener
{
	/**
	 * @var ContainerInterface
	 */
	private $container;

	/**
	 * GuidAwareListener constructor.
	 * @param ContainerInterface $container
	 */
	public function __construct(ContainerInterface $container)
	{
		$this->container = $container;
	}

	/**
	 * @param $entity
	 * @param LifecycleEventArgs $events
	 */
	public function prePersist($entity, LifecycleEventArgs $events)
	{
		if ($entity->getGuid() === null) {
			$entity->setGuid((string)$this->generate());
		}
	}

	/**
	 * @return UuidInterface
	 * @throws \Exception
	 */
	private static function generate()
	{
		try {
			$uuid = Uuid::uuid4();
			
			return $uuid;
		} catch (UnsatisfiedDependencyException $e) {
			//error symfo
		}
	}
}