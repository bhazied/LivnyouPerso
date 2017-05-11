<?php
/**
 * Created by PhpStorm.
 * User: dev03
 * Date: 11/05/17
 * Time: 17:08
 */

namespace ContinuousNet\LivnYouBundle\Repository\Criteria;

use ContinuousNet\LivnYouBundle\Repository\BaseRepository;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class OwnerCriteria extends BaseCriteria
{
    protected $autorisationChecker;

    protected $tokenStorage;

    public function __construct(AuthorizationCheckerInterface $autorisationChecker, TokenStorageInterface $tokenStorage)
    {
        $this->autorisationChecker = $autorisationChecker;

        $this->tokenStorage = $tokenStorage;
    }

    public function apply($queryBuilder, BaseRepository $repository)
    {
        if ($this->autorisationChecker->isGranted('ROLE_SUPER_ADMIN') || $this->autorisationChecker->isGranted('ROLE_ADMIN')) {
            return $queryBuilder;
        }
        $user = $this->tokenStorage->getToken()->getUser();
        /*$roles = $user->getRoles();
        if(!empty($roles)){
            foreach ($roles as $role) {
                if (substr_count($role, 'ACC') > 0) {
                    #oldSchool
                }
            }
        }*/
        $queryBuilder->andWhere($repository->alias().'.creatorUser = :creatorUser')->setParameter('creatorUser', $user->getId());
        return $queryBuilder;
    }
}
