<?php

namespace ContinuousNet\LivnYouBundle\Repository;

use ContinuousNet\LivnYouBundle\Repository\Join\UserJoin;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\PropertyAccess\PropertyAccess;

/**
 * Class UserRepository
 * @package ContinuousNet\LivnYouBundle\Repository
 */
class UserRepository extends BaseRepository
{
    protected $serchableFields = ['user.username', 'user.phone', 'user.email', 'user.usernameCanonical', 'user.emailCanonical', 'user.firstName', 'user.lastName', 'user.picture', 'user.address', 'user.zipCode', 'user.companyName', 'user.job', 'user.cityName', 'user.phoneValidationCode', 'user.emailValidationCode', 'user.roles', 'user.confirmationToken'];

    protected $countBy = 'id';

    public function alias()
    {
        return 'user';
    }

    public function initRepository()
    {
        $this->pushJoin(UserJoin::class);
    }

    /**
     * @param $entity
     * @param array $params
     * @return User
     */
    public function store($entity, $params= [])
    {
        $accessor = PropertyAccess::createPropertyAccessor();
        foreach ($params as $attribut => $value) {
            $accessor->setValue($entity, $attribut, $value);
        }
        $entity = $this->process($entity, true);
        $this->getEntityManager()->persist($entity);
        $this->getEntityManager()->flush();
        return $entity;
    }

    /**
     * @param $entity
     * @param array $params
     * @return User
     */
    public function update($entity, $params = [])
    {
        $accessor = PropertyAccess::createPropertyAccessor();
        foreach ($params as $attribut => $value) {
            $accessor->setValue($entity, $attribut, $value);
        }
        $entity = $this->process($entity, false);
        $this->getEntityManager()->flush();
        return $entity;
    }
    /**
     * @param User $entity
     * @param $isNew
     * @return User
     */
    private function process($entity, $isNew)
    {
        if (is_null($entity->getSalt()) || empty($entity->getSalt())) {
            $entity->setSalt(base_convert(sha1(uniqid(mt_rand(), true)), 16, 36));
        }
        if (is_null($entity->getRoles()) || empty($entity->getRoles())) {
            $entity->setRoles(array('ROLE_API', 'ROLE_ACCOUNT_MANAGER'));
        }
        $entity->setUsername($entity->getEmail());
        $entity->setUsernameCanonical(strtolower($entity->getEmail()));
        $entity->setEmailCanonical(strtolower($entity->getEmail()));
        if ($isNew || strlen($entity->getPassword()) != 88) {
            $entity->setPlainPassword($entity->getPassword());
        }
        return $entity;
    }
}
