<?php

namespace App\Service;

use App\Entity\User;
use Doctrine\Common\Annotations\AnnotationException;
use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
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
	 * Api constructor.
	 * @param ContainerInterface $container
	 * @param EntityManagerInterface $em
	 * @param SessionInterface $session
	 */
	public function __construct(ContainerInterface $container, EntityManagerInterface $em, SessionInterface $session)
	{
		$this->container = $container;
		$this->em = $em;
		$this->session = $session;
	}
	
	/**
	 * this method is used to test jwt and if the user is ok else send false
	 * @param string $infos_jwt
	 * @param string $token
	 * @return bool
	 * @throws \Exception
	 */
	public function userIslogged(string $infos_jwt, string $token): bool
	{
		$em = $this->em;
		$jwt = Jwt::decode($infos_jwt, $token);
		
		if ($jwt === false) {
			return false;
		}
		
		$this->user = $em->getRepository(User::class)->findOneBy([
			"token" => $token,
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
		
		return true;
	}
	
	/**
	 * method that return the token for a user
	 * @param User $user
	 * @return string
	 * @throws \Exception
	 */
	public function getToken(User $user): string
	{
		$token = $user->getToken();
		$now = new \DateTime();
		
		if ($token === null || $user->getEndToken() < $now) {
			return $this->setToken($user);
		}
		
		return $token;
	}
	
	/**
	 * @param User $user
	 * @return string
	 * method that set a toek for the user
	 * @throws \Exception
	 */
	public function setToken(User $user): string
	{
		$token = $this->generateToken();
		$now = new \DateTime();
		$end_token = $now->add(new \DateInterval('PT20M'));
		
		$user->setToken($token);
		$user->setEndToken($end_token);
		
		$this->em->persist($user);
		$this->em->flush();
		
		$this->user = $user;
		$this->session->set("user", $this->user);
		
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