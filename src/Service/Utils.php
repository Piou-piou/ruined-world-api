<?php

namespace App\Service;

use Symfony\Component\DependencyInjection\ContainerInterface;

class Utils
{
	/**
	 * @var ContainerInterface
	 */
	private $container;
	
	/**
	 * Utils constructor.
	 * @param ContainerInterface $container
	 */
	public function __construct(ContainerInterface $container)
	{
		$this->container = $container;
	}
}