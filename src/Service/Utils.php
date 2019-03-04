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
	
	/**
	 * method that create a tree of folders on each slash
	 *
	 * @param $path
	 * @return string
	 */
	public function createRecursiveDirFromRoot($path)
	{
		$fs = new Filesystem();
		$root_dir = $this->container->get("kernel")->getProjectDir();
		$new_path = $root_dir;
		$folders = explode("/", $path);
		
		foreach ($folders as $index => $folder) {
			$new_path .= $folder;
			
			if (!$fs->exists($new_path)) {
				$fs->mkdir($new_path);
			}
			
			if ($index + 1 < count($folders)) {
				$new_path .= "/";
			}
		}
		
		return $new_path;
	}
}