<?php

namespace ContinuousNet\LivnYouBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Symfony\Component\PropertyAccess\PropertyAccess;

/**
 * Class UserRepository
 * @package ContinuousNet\LivnYouBundle\Repository
 */
class UserRepository extends EntityRepository implements IRepository
{


    /**
     * @param array $params
     * @return mixed
     */
    public function getAll($params = [])
    {
        $qBuilder = $this->createQueryBuilder('user');
        $qBuilder->leftJoin('ContinuousNet\LivnYouBundle\Entity\Country', 'country', \Doctrine\ORM\Query\Expr\Join::WITH, 'user.country = country.id');
        $qBuilder->leftJoin('ContinuousNet\LivnYouBundle\Entity\Language', 'language', \Doctrine\ORM\Query\Expr\Join::WITH, 'user.language = language.id');
        $qBuilder->leftJoin('ContinuousNet\LivnYouBundle\Entity\User', 'creator_user', \Doctrine\ORM\Query\Expr\Join::WITH, 'user.creatorUser = creator_user.id');
        $qBuilder->leftJoin('ContinuousNet\LivnYouBundle\Entity\User', 'modifier_user', \Doctrine\ORM\Query\Expr\Join::WITH, 'user.modifierUser = modifier_user.id');
        $textFields = array('user.username', 'user.phone', 'user.email', 'user.usernameCanonical', 'user.emailCanonical', 'user.firstName', 'user.lastName', 'user.picture', 'user.address', 'user.zipCode', 'user.companyName', 'user.job', 'user.cityName', 'user.phoneValidationCode', 'user.emailValidationCode', 'user.roles', 'user.confirmationToken');
        $memberOfConditions = array();
        foreach ($params['filters'] as $field => $value) {
            if (substr_count($field, '.') > 1) {
                if ($value == 'true' || $value == 'or' || $value == 'and') {
                    list($entityName, $listName, $listItem) = explode('.', $field);
                    if (!isset($memberOfConditions[$listName])) {
                        $entityName = null;
                        $memberOfConditions[$listName] = array('items' => array(), 'operator' => 'or');
                    }
                    if ($value == 'or' || $value == 'and') {
                        $memberOfConditions[$listName]['operator'] = $value;
                    } else {
                        $memberOfConditions[$listName]['items'][] = $listItem;
                    }
                }
                continue;
            }
            $key = str_replace('.', '', $field);
            if (!empty($value)) {
                if (in_array($field, $textFields)) {
                    if (isset($params['filterOperators'][$field]) && $params['filterOperators'][$field] == 'eq') {
                        $qBuilder->andWhere($qBuilder->expr()->eq($field, $qBuilder->expr()->literal($value)));
                    } else {
                        $qBuilder->andWhere($qBuilder->expr()->like($field, $qBuilder->expr()->literal('%' . $value . '%')));
                    }
                } else {
                    $qBuilder->andWhere($field.' = :'.$key.'')->setParameter($key, $value);
                }
            }
        }
        foreach ($memberOfConditions as $listName => $memberOfCondition) {
            if (!empty($memberOfCondition['items'])) {
                if ($memberOfCondition['operator'] == 'or') {
                    $orX = $qBuilder->expr()->orX();
                    foreach ($memberOfCondition['items'] as $i => $item) {
                        $orX->add($qBuilder->expr()->isMemberOf(':'.$listName.'_value_'.$i, 'user.'.$listName));
                        $qBuilder->setParameter($listName.'_value_'.$i, $item);
                    }
                    $qBuilder->andWhere($orX);
                } elseif ($memberOfCondition['operator'] == 'and') {
                    $andX = $qBuilder->expr()->andX();
                    foreach ($memberOfCondition['items'] as $i => $item) {
                        $andX->add($qBuilder->expr()->isMemberOf(':'.$listName.'_value_'.$i, 'user.'.$listName));
                        $qBuilder->setParameter($listName.'_value_'.$i, $item);
                    }
                    $qBuilder->andWhere($andX);
                }
            }
        }
        $qbList = clone $qBuilder;
        $qBuilder->select('count(user.id)');
        $data['inlineCount'] = $qBuilder->getQuery()->getSingleScalarResult();
        foreach ($params['orderBy'] as $field => $direction) {
            $qbList->addOrderBy($field, $direction);
        }
        $qbList->select('user');
        $qbList->setMaxResults($params['limit']);
        $qbList->setFirstResult($params['offset']);
        $qbList->groupBy('user.id');
        $results = $qbList->getQuery()->getResult();
        $data['results'] = $results;
        return $data;
    }

    /**
     * @param array $params
     * @return null|object
     */
    public function get($params = [])
    {
        return $this->findOneBy($params);
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
     * @param $idEntity
     */
    public function delete($idEntity)
    {
        $entity = $this->find($idEntity);
        $this->getEntityManager()->remove($entity);
        $this->getEntityManager()->flush();
    }

    public function count($params = [])
    {
        $qBuilder = $this->createQueryBuilder('country');
        if (array_key_exists('filters', $params)) {
            foreach ($params['filters'] as $fKey => $value) {
                $qBuilder->andWhere('country.'.$fKey .'=:'.$fKey)->setParameter($fKey, $value);
            }
        }
        $qBuilder->select('count(country.id)');
        return $qBuilder->getQuery()->getSingleScalarResult();
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
