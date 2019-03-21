<?php

namespace App\Service;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Filesystem\Filesystem;

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
		$root_dir = $this->container->get("kernel")->getProjectDir() . "/";
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
	
	/**
	 * method that generate a uniq guid
	 * @return string
	 */
	public function gen_uuid(): string
	{
		return sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
			// 32 bits for "time_low"
			mt_rand(0, 0xffff), mt_rand(0, 0xffff),
			
			// 16 bits for "time_mid"
			mt_rand(0, 0xffff),
			
			// 16 bits for "time_hi_and_version",
			// four most significant bits holds version number 4
			mt_rand(0, 0x0fff) | 0x4000,
			
			// 16 bits, 8 bits for "clk_seq_hi_res",
			// 8 bits for "clk_seq_low",
			// two most significant bits holds zero and one for variant DCE1.1
			mt_rand(0, 0x3fff) | 0x8000,
			
			// 48 bits for "node"
			mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
		);
	}
}