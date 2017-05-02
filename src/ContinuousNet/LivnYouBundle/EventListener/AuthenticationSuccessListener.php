<?php

namespace ContinuousNet\LivnYouBundle\EventListener;

use FOS\UserBundle\Model\UserManager;
use FOS\UserBundle\Model\UserManagerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Event\AuthenticationSuccessEvent;
use Symfony\Component\Security\Core\User\UserInterface;

class AuthenticationSuccessListener
{
    private $userManager;

    public function __construct(UserManagerInterface $_userManager)
    {
        $this->userManager = $_userManager;
    }

    public function onAuthenticationSuccessResponse(AuthenticationSuccessEvent $event)
    {
        $data = $event->getData();
        $user = $event->getUser();
        if (!$user instanceof UserInterface) {
            return;
        }
        $currentUser = $this->userManager->findUserByEmail($user->getUsername());
        $data['user'] = $this->parseUserResponse($currentUser);
        $this->updateUser($currentUser);
        $event->setData($data);
    }

    private function updateUser(\ContinuousNet\LivnYouBundle\Entity\User $user)
    {
        $user->setLoginCount($user->getLoginCount() + 1);
        $user->setFailedLoginCount(0);
        $this->userManager->updateUser($user);
    }

    private function parseUserResponse(\ContinuousNet\LivnYouBundle\Entity\User $user)
    {
        return  [
            'first_name' => $user->getFirstName(),
            'last_name' => $user->getLastName(),
            'name' => $user->getFirstName().' '.$user->getLastName(),
            'email' => $user->getEmail(),
            'picture' => $user->getPicture(),
            'job' => $user->getJob(),
            'roles' => $user->getRoles(),
            'country' => $user->getCountry(),
            //'country' => $user->getCountry()->getId(),
            //'countryName' => $user->getCountry()->getName(),
            'phone' => $user->getPhone(),
            'gender' => $user->getGender(),
            'picture' => $user->getPicture(),
            'type' => $user->getType(),
        ];
    }
}
