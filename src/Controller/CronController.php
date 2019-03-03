<?php

namespace App;

use Cron\CronExpression;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class CronController extends AbstractController
{
	private $crons;
	
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
					$this->ajoutEntreeJson($key);
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
					$this->editEntreeJson($key, $cron->getNextRunDate()->format('Y-m-d H:i:s'));
				}
			}
		}
		
		return new Response();
	}
	
	/**
	 * return the json file with all crons in it. If not exist, we create it add put cron llike this :
	 * key => nameOfMethodToExecute
	 * [last_execution = null]
	 * @return mixed|string
	 */
	private function getCronFile()
	{
		$file = $this->getParameter("data_directory") . "cron/cron.json";
		
		if (!is_file($file)) {
			//$this->get("app.utils")->createRecursiveDirFromRoot('data/cron');
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
	private function ajoutEntreeJson(string $entry)
	{
		$file = $this->getParameter("data_directory") . "cron/cron.json";
		$crons = json_decode(file_get_contents($file), true);
		
		$crons[$entry] = [
			"next_execution" => null
		];
		
		$this->writeCronJson($crons);
	}
	
	/**
	 * method to edit an entry in json
	 * @param string $entry
	 * @param string $next_execution
	 */
	private function editEntreeJson(string $entry, string $next_execution)
	{
		$json = $this->getFichierCron();
		
		if (array_key_exists($entry, $json)) {
			$json[$entry]["next_execution"] = $next_execution;
			
			$this->writeCronJson($json);
		}
	}
	
	/**
	 * method that writes the cron.json when we add or edit an entry
	 * @param array $json
	 */
	private function writeCronJson(array $json)
	{
		$fs = new Filesystem();
		$file = $this->getParameter("data_directory") . "cron/cron.json";
		
		$fs->dumpFile($file, json_encode($json));
	}
}