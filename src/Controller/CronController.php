<?php

namespace App\Controller;

use App\Entity\Base;
use App\Service\Resources;
use App\Service\Utils;
use Cron\CronExpression;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\Routing\Annotation\Route;

class CronController extends AbstractController
{
	/**
	 * @var Utils
	 */
	private $utils;
	
	/**
	 * @var Resources
	 */
	private $resources;
	
	private $crons;
	
	/**
	 * CronController constructor.
	 * @param Utils $utils
	 * @param Resources $resources
	 */
	public function __construct(Utils $utils, Resources $resources)
	{
		$this->utils = $utils;
		$this->resources = $resources;
	}
	
	/**
	 * @Route("/cron", name="cron")
	 * @param Request $request
	 * @return Response
	 * @throws \Exception
	 */
	public function cron(Request $request)
	{
		$ip = $request->server->get('REMOTE_ADDR');
		$allowed_ip = ["127.0.0.1"];
		
		if (in_array($ip, $allowed_ip)) {
			$this->crons = $this->getParameter("cron");
			$json_exec = $this->getCronFile();
			$now = new \DateTime();
			
			// start executing crons
			foreach ($this->crons as $key => $cron) {
				if (!array_key_exists($key, $json_exec)) {
					$this->addJsonEntry($key);
					$json_exec = $this->getCronFile();
				}
				
				$next_exec = $json_exec[$key]["next_execution"];
				if (method_exists($this, $key)) {
					if ($next_exec === null) {
						$this->$key();
					} else if ($now >= \DateTime::createFromFormat("Y-m-d H:i:s", $next_exec)) {
						$this->$key();
					}
					
					$cron = CronExpression::factory($this->getParameter("cron")[$key]);
					$this->editJsonEntry($key, $cron->getNextRunDate()->format('Y-m-d H:i:s'));
				}
			}
		} else {
			throw new AccessDeniedHttpException("You haven't got access to this page");
		}
		
		return new Response();
	}
	
	/**
	 * return the json file with all crons in it. If not exist, we create it add put cron like this :
	 * key => nameOfMethodToExecute
	 * [last_execution = null]
	 * @return mixed|string
	 */
	private function getCronFile()
	{
		$file = $this->getParameter("data_directory") . "cron/cron.json";
		
		if (!is_file($file)) {
			$this->utils->createRecursiveDirFromRoot('data/cron');
			$fs = new Filesystem();
			$fs->touch($this->getParameter("data_directory") . "cron/cron.json");
			
			$crons = [];
			
			foreach ($this->crons as $key => $cron) {
				$crons[$key] = [
					"next_execution" => null
				];
			}
			
			$fs->appendToFile($file, json_encode($crons));
		}
		
		$file = json_decode(file_get_contents($file), true);
		
		return $file;
	}
	
	/**
	 * method that add new entry in config cron file
	 * @param string $entry
	 */
	private function addJsonEntry(string $entry)
	{
		$file = $this->getParameter("data_directory") . "cron/cron.json";
		$crons = json_decode(file_get_contents($file), true);
		
		$crons[$entry] = [
			"next_execution" => null
		];
		
		$this->writeJsonCron($crons);
	}
	
	/**
	 * method to edit an entry in json
	 * @param string $entry
	 * @param string $next_execution
	 */
	private function editJsonEntry(string $entry, string $next_execution)
	{
		$json = $this->getCronFile();
		
		if (array_key_exists($entry, $json)) {
			$json[$entry]["next_execution"] = $next_execution;
			
			$this->writeJsonCron($json);
		}
	}
	
	/**
	 * method that writes the cron.json when we add or edit an entry
	 * @param array $json
	 */
	private function writeJsonCron(array $json)
	{
		$fs = new Filesystem();
		$file = $this->getParameter("data_directory") . "cron/cron.json";
		
		$fs->dumpFile($file, json_encode($json));
	}
	
	
	// --------------------------------------- UNDER THIS, METHODS OF CRONS ----------------------------------------------------//
	
	/**
	 * method that update resources of a base based on resources produced by hour. This method is called every minute
	 * @throws \Exception
	 */
	public function updateResources()
	{
		$em = $this->getDoctrine()->getManager();
		
		$bases = $em->getRepository(Base::class)->findAll();
		$session = new Session();
		
		foreach ($bases as $base) {
			$session->set("current_base", $base);
			$session->set("token", $base->getUser()->getToken());
			
			$now = new \DateTime();
			$last_update_resources = $base->getLastUpdateResources();
			$diff = $now->getTimestamp() - $last_update_resources->getTimestamp();
			
			$this->resources->addResource("electricity", round((100 / 3600) * $diff));
			$this->resources->addResource("fuel", round((100 / 3600) * $diff));
			$this->resources->addResource("iron", round((100 / 3600) * $diff));
			$this->resources->addResource("water", round((100 / 3600) * $diff));
			
			$base->setLastUpdateResources($now);
			$em->flush();
		}
	}
}