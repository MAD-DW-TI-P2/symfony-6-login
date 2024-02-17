<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\HttpFoundation\Request;
use App\Repository\UserRepository;

class SecurityController extends AbstractController
{
    #[Route(path: '/login', name: 'app_login')]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        // if ($this->getUser()) {
        //     return $this->redirectToRoute('target_path');
        // }

        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();
        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('security/login.html.twig', ['last_username' => $lastUsername, 'error' => $error]);
    }

    #[Route(path: '/logout', name: 'app_logout')]
    public function logout(): void
    {
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }

    #[Route(path: '/apicheck', name: 'api_check')]
    public function apicheck(): Response
    {
        return $this->json(['pass'=> 'Acceso permitido por token'], $status = 200, $headers = ['Access-Control-Allow-Origin'=>'*']);
    }

    #[Route(path: '/apicheckinfo', name: 'api_check_info')]
    public function apicheckinfo(Request $request, UserRepository $userRepository): Response
    {
        $token = $request->headers->get('Authorization');

        $tokenParts = explode(".", $token);
        $tokenHeader = base64_decode($tokenParts[0]);
        $tokenPayload = base64_decode($tokenParts[1]);
        $jwtHeader = json_decode($tokenHeader);
        $jwtPayload = json_decode($tokenPayload);

        $user = $userRepository->findOneByEmail($jwtPayload->username);

        if (!$user) {
            return $this->json(['error'=> 'Acceso denegado'], $status = 401, $headers = ['Access-Control-Allow-Origin'=>'*']);
        }

        return $this->json(['pass'=> 'Acceso permitido por token a' . $jwtPayload->username. 'con el rol '. $user->getRoles()[0]], $status = 200, $headers = ['Access-Control-Allow-Origin'=>'*']);
    }
}
