<?php
namespace ContinuousNet\LivnYouBundle\EventListener;

use ContinuousNet\LivnYouBundle\Repository\IRepository;
use FOS\UserBundle\Model\UserManagerInterface;
use GuzzleHttp\Psr7\Request;
use Lexik\Bundle\JWTAuthenticationBundle\Event\JWTExpiredEvent;
use Symfony\Component\HttpFoundation\RequestStack;

class JWTExpiredTokenListener {

    private $requestStack;

    private $sessionRepository;


    public function __construct(RequestStack $_requestStack, IRepository $_sessionrepository)
    {
        $this->requestStack = $_requestStack;

        $this->sessionRepository  = $_sessionrepository;
        
    }

    public function  onJWTExpired(JWTExpiredEvent $event){

        $user = $this->requestStack->getCurrentRequest()->getUser();
        if($user){
            $sessions = $this->sessionRepository->findBy(['creatorUser' => $user->getId(), 'isValid' => true]);
            foreach ($sessions as $sess){
                $sess->setIsValid(false);
                $this->sessionRepository->update($sess);
            }
        }
    }
}
?>