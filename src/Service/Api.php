<?php

namespace App\Service;

use App\Entity\User;
use App\Entity\UserToken;
use Doctrine\Common\Annotations\AnnotationException;
use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Encoder\XmlEncoder;
use Symfony\Component\Serializer\Exception\ExceptionInterface;
use Symfony\Component\Serializer\Mapping\Factory\ClassMetadataFactory;
use Symfony\Component\Serializer\Mapping\Loader\AnnotationLoader;
use Symfony\Component\Serializer\Normalizer\DateTimeNormalizer;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

class Api
{
	/**
	 * @var ContainerInterface
	 */
	private $container;
	
	/**
	 * @var EntityManagerInterface
	 */
	private $em;
	
	/**
	 * @var SessionInterface
	 */
	private $session;
	
	/**
	 * @var User
	 */
	private $user;

	/**
	 * @var Request|null
	 */
	private $request;

	/**
	 * Api constructor.
	 * @param ContainerInterface $container
	 * @param EntityManagerInterface $em
	 * @param SessionInterface $session
	 * @param RequestStack $request_stack
	 */
	public function __construct(ContainerInterface $container, EntityManagerInterface $em, SessionInterface $session, RequestStack $request_stack)
	{
		$this->container = $container;
		$this->em = $em;
		$this->session = $session;
		$this->request = $request_stack->getCurrentRequest();
	}
	
	/**
	 * this method is used to test jwt and if the user is ok else send false
	 * @param string $infos_jwt
	 * @param string $token
	 * @return bool
	 * @throws Exception
	 */
	public function userIslogged(string $infos_jwt, string $token): bool
	{
		$em = $this->em;
		$jwt = Jwt::decode($infos_jwt, $token);
		
		if ($jwt === false) {
			return false;
		}

		$user_token = $em->getRepository(UserToken::class)->findOneBy([
			"token" => $token,
			"userAgent" => $this->request->server->get("HTTP_USER_AGENT"),
			"ip" => $this->request->server->get("REMOTE_ADDR")
		]);

		if (!$user_token) {
			return false;
		}
		
		$this->user = $em->getRepository(User::class)->findOneBy([
			"id" => $user_token->getUser()->getId(),
			"archived" => false,
		]);
		
		if (!$this->user) {
			return false;
		}
		
		$this->user->setLastConnection(new \DateTime());
		$em->persist($this->user);
		$em->flush();
		
		$this->getToken($this->user);
		$this->session->set("jwt_infos", $jwt);
		$this->session->set("user", $this->user);
		$this->session->set("user_token", $user_token);

		return true;
	}

	/**
	 * method that return the token for a user
	 * @param User $user
	 * @return string
	 * @throws Exception
	 */
	public function getToken(User $user): string
	{
		$user_token = $this->em->getRepository(UserToken::class)->findOneBy([
			"user" => $user,
			"userAgent" => $this->request->server->get("HTTP_USER_AGENT"),
			"ip" => $this->request->server->get("REMOTE_ADDR")
		]);

		$token = $user_token ? $user_token->getToken() : null;
		$now = new \DateTime();
		
		if ($token === null || $user_token->getEndToken() < $now) {
			return $this->setToken($user, $user_token);
		}
		
		return $token;
	}

	/**
	 * @param User $user
	 * @param $user_token
	 * @return string
	 * method that set a toek for the user
	 * @throws Exception
	 */
	public function setToken(User $user, $user_token): string
	{
		$token = $this->generateToken();
		$now = new \DateTime();
		$end_token = $now->add(new \DateInterval("PT".$this->container->getParameter("api_token_duration")."M"));

		if (!$user_token) {
			$user_token = new UserToken();
		}

		$user_token->setToken($token);
		$user_token->setUserAgent($this->request->server->get("HTTP_USER_AGENT"));
		$user_token->setIp($this->request->server->get("REMOTE_ADDR"));
		$user_token->setEndToken($end_token);
		$user_token->setUser($user);
		$this->em->persist($user_token);
		$this->em->flush();
		
		$this->user = $user;
		$this->session->set("user", $this->user);
		$this->session->set("user_token", $user_token);

		return $token;
	}
	
	/**
	 * generate a token for api
	 * @param int $length
	 * @return string
	 */
	public function generateToken(int $length = 200): string
	{
		$string = "abcdefghijklmnopqrstuvwxyzABCDEFGHJKMNPQRSTUVWXYZ23456789";
		$token = "";
		srand((double)microtime() * 1000000);
		for ($i = 0; $i < $length; $i++) {
			$token .= $string[rand() % strlen($string)];
		}
		
		return $token;
	}

	/**
	 * method that encode an object to a json
	 * @param $object
	 * @param string $type
	 * @return mixed
	 * @throws ExceptionInterface
	 * @throws AnnotationException
	 */
	public function serializeObject($object, $type = "json")
	{
		$classMetadataFactory = new ClassMetadataFactory(new AnnotationLoader(new AnnotationReader()));
		$serializer = new Serializer([new DateTimeNormalizer(), new ObjectNormalizer($classMetadataFactory)], [new XmlEncoder(), new JsonEncoder()]);
		
		return $serializer->normalize($object, $type, [
			'circular_reference_handler' => function ($object) {
				return $object->getId();
			},
			'groups' => 'main'
		]);
	}
}