<?php

namespace App\Service;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class AccessRights
{
	/**
	 * @var ContainerInterface
	 */
	private $container;
	
	/**
	 * @var null|\Symfony\Component\HttpFoundation\Request
	 */
	private $request;
	
	/**
	 * @var Api
	 */
	private $api;
	
	/**
	 * AccessRights constructor.
	 * @param ContainerInterface $container
	 * @param RequestStack $request_stack
	 * @param Api $api
	 */
	public function __construct(ContainerInterface $container, RequestStack $request_stack, Api $api)
	{
		$this->container = $container;
		$this->request = $request_stack->getCurrentRequest();
		$this->api = $api;
	}
	
	/**
	 * method that test if user is connected and jwt infos are correct
	 * if not send an http exception else set two session :
	 * - one with jwt infos
	 * - second with User class
	 * @throws \Exception
	 */
	public function onKernelController()
	{
		$route = $this->request->get("_route");
		
		if (in_array($route, $this->container->getParameter("route_rights_exluded"))) return;
		
		if ($this->api->userIslogged($this->request->get("infos"), $this->request->get("token")) === false) {
			throw new AccessDeniedHttpException("User is not connected");
		}
	}
}