<?php

namespace ContinuousNet\LivnYouBundle\EventListener;


use Lexik\Bundle\JWTAuthenticationBundle\Event\JWTCreatedEvent;
use Symfony\Component\HttpFoundation\RequestStack;

class JWTCreatedListener{

    private $requestStack;

    public function __construct(RequestStack $_requestStack)
    {
        $this->requestStack = $_requestStack;
    }

    public function onJWTCreated(JWTCreatedEvent $event){

      /*
       * No need fo this in this moment
       */
    }
}

?>