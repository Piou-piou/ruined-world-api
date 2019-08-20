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

		$user_token_search = [
			"token" => $token,
			"userAgent" => $this->request->server->get("HTTP_USER_AGENT"),
			"ip" => $this->request->server->get("REMOTE_ADDR")
		];
		if (preg_match('/(android|bb\d+|meego).+mobile|avantgo|bada\/|blackberry|blazer|compal|elaine|fennec|hiptop|iemobile|ip(hone|od)|iris|kindle|lge |maemo|midp|mmp|netfront|opera m(ob|in)i|palm( os)?|phone|p(ixi|re)\/|plucker|pocket|psp|series(4|6)0|symbian|treo|up\.(browser|link)|vodafone|wap|windows (ce|phone)|xda|xiino/i',$this->request->server->get("HTTP_USER_AGENT"))||preg_match('/1207|6310|6590|3gso|4thp|50[1-6]i|770s|802s|a wa|abac|ac(er|oo|s\-)|ai(ko|rn)|al(av|ca|co)|amoi|an(ex|ny|yw)|aptu|ar(ch|go)|as(te|us)|attw|au(di|\-m|r |s )|avan|be(ck|ll|nq)|bi(lb|rd)|bl(ac|az)|br(e|v)w|bumb|bw\-(n|u)|c55\/|capi|ccwa|cdm\-|cell|chtm|cldc|cmd\-|co(mp|nd)|craw|da(it|ll|ng)|dbte|dc\-s|devi|dica|dmob|do(c|p)o|ds(12|\-d)|el(49|ai)|em(l2|ul)|er(ic|k0)|esl8|ez([4-7]0|os|wa|ze)|fetc|fly(\-|_)|g1 u|g560|gene|gf\-5|g\-mo|go(\.w|od)|gr(ad|un)|haie|hcit|hd\-(m|p|t)|hei\-|hi(pt|ta)|hp( i|ip)|hs\-c|ht(c(\-| |_|a|g|p|s|t)|tp)|hu(aw|tc)|i\-(20|go|ma)|i230|iac( |\-|\/)|ibro|idea|ig01|ikom|im1k|inno|ipaq|iris|ja(t|v)a|jbro|jemu|jigs|kddi|keji|kgt( |\/)|klon|kpt |kwc\-|kyo(c|k)|le(no|xi)|lg( g|\/(k|l|u)|50|54|\-[a-w])|libw|lynx|m1\-w|m3ga|m50\/|ma(te|ui|xo)|mc(01|21|ca)|m\-cr|me(rc|ri)|mi(o8|oa|ts)|mmef|mo(01|02|bi|de|do|t(\-| |o|v)|zz)|mt(50|p1|v )|mwbp|mywa|n10[0-2]|n20[2-3]|n30(0|2)|n50(0|2|5)|n7(0(0|1)|10)|ne((c|m)\-|on|tf|wf|wg|wt)|nok(6|i)|nzph|o2im|op(ti|wv)|oran|owg1|p800|pan(a|d|t)|pdxg|pg(13|\-([1-8]|c))|phil|pire|pl(ay|uc)|pn\-2|po(ck|rt|se)|prox|psio|pt\-g|qa\-a|qc(07|12|21|32|60|\-[2-7]|i\-)|qtek|r380|r600|raks|rim9|ro(ve|zo)|s55\/|sa(ge|ma|mm|ms|ny|va)|sc(01|h\-|oo|p\-)|sdk\/|se(c(\-|0|1)|47|mc|nd|ri)|sgh\-|shar|sie(\-|m)|sk\-0|sl(45|id)|sm(al|ar|b3|it|t5)|so(ft|ny)|sp(01|h\-|v\-|v )|sy(01|mb)|t2(18|50)|t6(00|10|18)|ta(gt|lk)|tcl\-|tdg\-|tel(i|m)|tim\-|t\-mo|to(pl|sh)|ts(70|m\-|m3|m5)|tx\-9|up(\.b|g1|si)|utst|v400|v750|veri|vi(rg|te)|vk(40|5[0-3]|\-v)|vm40|voda|vulc|vx(52|53|60|61|70|80|81|83|85|98)|w3c(\-| )|webc|whit|wi(g |nc|nw)|wmlb|wonu|x700|yas\-|your|zeto|zte\-/i',substr($this->request->server->get("HTTP_USER_AGENT"),0,4))) {
			$user_token_search = [
				"token" => $token,
				"userAgent" => $this->request->server->get("HTTP_USER_AGENT"),
			];
		}

		$user_token = $em->getRepository(UserToken::class)->findOneBy($user_token_search);

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