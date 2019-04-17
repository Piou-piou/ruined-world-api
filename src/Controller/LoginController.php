<?php

namespace App\Controller;

use App\Entity\User;
use App\Service\Api;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Routing\Annotation\Route;

class LoginController extends AbstractController
{
    /**
     * this method is user to authenticate a user by an api request
     * if success it return a token api that expire in 20 minutes
     * @Route("/api/users/authenticate", name="api_login", methods={"POST"})
     * @param Request $request
     * @param Api $api
     * @return JsonResponse
     * @throws \Exception
     */
    public function login(Request $request, Api $api): JsonResponse
    {
        $em = $this->getDoctrine()->getManager();

        /**
         * @var User $account
         */
        $account = $em->getRepository(User::class)->findOneBy([
            "pseudo" => $request->get("username"),
        ]);

        if ($account) {
            $encoder = $this->get("security.password_encoder");

            if ($encoder->isPasswordValid($account, $request->get("password")) === true) {

                if ($account->getisActive() == false) {
                    return new JsonResponse([
                        "success" => false,
                        "message" => "You account is disabled"
                    ]);
                }

                return new JsonResponse([
                    "success" => true,
                    "token" => $api->getToken($account)
                ]);
            }
        }

        return new JsonResponse([
            "success" => false,
            "message" => "bad identifiant and/or password"
        ]);
    }

    /**
     * method that test if user steel logged and send token or new token if it was expired
     * @Route("/api/users/test-token", name="api_tets_token", methods={"POST"})
     * @param Request $request
     * @param Api $api
     * @param Session $session
     * @return JsonResponse
     * @throws \Exception
     */
    public function testUserToken(Request $request, Api $api, Session $session): JsonResponse
    {
        return new JsonResponse([
            "success" => $api->userIslogged($request->get("infos"), $request->get("token")),
            "token" => $api->getToken($session->get("user"))
        ]);
    }
}