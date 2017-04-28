<?php

namespace ContinuousNet\LivnYouBundle\EventListener;


use ContinuousNet\LivnYouBundle\Repository\IRepository;
use ContinuousNet\LivnYouBundle\Repository\SessionRepository;
use ContinuousNet\LivnYouBundle\Entity\Session;
use FOS\UserBundle\Model\UserManagerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Event\JWTCreatedEvent;
use Symfony\Component\HttpFoundation\RequestStack;

class JWTCreatedListener{

    private $requestStack;

    private $sessionRepository;

    private $userManager;

    public function __construct(RequestStack $_requestStack, IRepository $_sessionRepository, UserManagerInterface $_userManager)
    {
        $this->requestStack = $_requestStack;
        $this->sessionRepository = $_sessionRepository;
        $this->userManager = $_userManager;
    }

    public function onJWTCreated(JWTCreatedEvent $event){

      /*
       * No need fo this in this moment
       */
        $user = $this->userManager->findUserByEmail($event->getUser()->getUsername());
        $sessions = $this->sessionRepository->findBy(['creatorUser' => $user->getId()]);
        foreach($sessions as $sess) {
            $sess->setIsValid(false);
            $this->sessionRepository->update($sess);
        }
        $session = new Session();
        $session->setIp($this->requestStack->getCurrentRequest()->getClientIp());
        $session->setUserAgent($this->requestStack->getCurrentRequest()->headers->get('User-Agent'));
        $session->setIsValid(true);
        $this->sessionRepository->store($session, ['creatorUser' => $user]);

    }
}

?>