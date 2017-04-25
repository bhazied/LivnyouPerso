<?php

namespace ContinuousNet\LivnYouBundle\EventListener;


use FOS\UserBundle\Model\UserManagerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Response\JWTAuthenticationFailureResponse;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Lexik\Bundle\JWTAuthenticationBundle\Event\AuthenticationFailureEvent;
use Symfony\Component\Translation\TranslatorInterface;

class AuthenticationFailureListener {

    private $userManager;

    private $request;

    private $translator;

    public function __construct(UserManagerInterface $_userManager, RequestStack $_request, TranslatorInterface $_translator)
    {
        $this->userManager = $_userManager;
        $this->request = $_request;
        $this->translator = $_translator;
    }

    public function onAuthenticationFailureResponse(AuthenticationFailureEvent $event){

        $user_email = $this->request->getCurrentRequest()->request->get('email');
        $user = $this->userManager->findUserByEmail($user_email);
        if(!is_null($user)){
            $user->setFailedLoginCount($user->getFailedLoginCount()+1);
            $user->setLastFailedLogin(new \DateTime('now'));
            $user->setLastFailedLoginCount($user->getLastFailedLoginCount()+1);
            $this->userManager->updateUser($user);
        }
        $data = [
            'status' => '401',
            'message' => $this->translator->trans('authentication.failure.message')
        ];
        $response = new JWTAuthenticationFailureResponse($data);
        $event->setResponse($response);

    }
}
?>