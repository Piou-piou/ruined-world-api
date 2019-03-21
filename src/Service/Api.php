<?php

namespace App\Service;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Encoder\XmlEncoder;
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
	 * @var array
	 */
	private $jwtInfos;
	
	/**
	 * @var User
	 */
	private $user;
	
	/**
	 * Api constructor.
	 * @param ContainerInterface $container
	 * @param EntityManagerInterface $em
	 */
	public function __construct(ContainerInterface $container, EntityManagerInterface $em)
	{
		$this->container = $container;
		$this->em = $em;
	}
	
	/**
	 * method that return current user
	 * @return mixed
	 */
	public function getUser()
	{
		return $this->user;
	}
	
	/**
	 * method that return jwt infos
	 * @return mixed
	 */
	public function getJwtInfos()
	{
		return $this->jwtInfos;
	}
	
	/**
	 * @param string $infos_jwt
	 * @param string $token
	 * @return bool
	 * this method is used to test jwt and if the user is ok else send false
	 * @throws \Exception
	 */
	public function userIslogged(string $infos_jwt, string $token): bool
	{
		$em = $this->em;
		$jwt = Jwt::decode($infos_jwt, $token);
		
		if ($jwt === false) {
			return false;
		}
		
		$user = $em->getRepository(User::class)->findOneBy(["token" => $token]);
		
		if (!$user) {
			return false;
		}
		
		$this->getToken($user);
		$this->jwtInfos = $jwt;
		
		return true;
	}
	
	/**
	 * @param User $user
	 * @return string
	 * method that return the token for a user
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
		
		return $token;
	}
	
	/**
	 * @param int $length
	 * @return string
	 * generate a token for api
	 */
	private function generateToken(int $length = 200): string
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
	 */
	public function serializeObject($object, $type = "json")
	{
		$serializer = new Serializer([new ObjectNormalizer()], [new XmlEncoder(), new JsonEncoder()]);
		
		return $serializer->serialize($object, $type);
	}
}