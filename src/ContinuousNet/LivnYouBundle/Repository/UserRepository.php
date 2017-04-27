<?php

namespace ContinuousNet\LivnYouBundle\Repository;

use Doctrine\ORM\EntityRepository;

/**
 * Class UserRepository
 * @package ContinuousNet\LivnYouBundle\Repository
 */
class UserRepository extends EntityRepository implements IRepository{


    /**
     * @param array $params
     * @return mixed
     */
    public function getAll($params = []){

        $qb = $this->createQueryBuilder('user');
        $qb->leftJoin('ContinuousNet\LivnYouBundle\Entity\Country', 'country', \Doctrine\ORM\Query\Expr\Join::WITH, 'user.country = country.id');
        $qb->leftJoin('ContinuousNet\LivnYouBundle\Entity\Language', 'language', \Doctrine\ORM\Query\Expr\Join::WITH, 'user.language = language.id');
        $qb->leftJoin('ContinuousNet\LivnYouBundle\Entity\User', 'creator_user', \Doctrine\ORM\Query\Expr\Join::WITH, 'user.creatorUser = creator_user.id');
        $qb->leftJoin('ContinuousNet\LivnYouBundle\Entity\User', 'modifier_user', \Doctrine\ORM\Query\Expr\Join::WITH, 'user.modifierUser = modifier_user.id');
        $textFields = array('user.username', 'user.phone', 'user.email', 'user.usernameCanonical', 'user.emailCanonical', 'user.firstName', 'user.lastName', 'user.picture', 'user.address', 'user.zipCode', 'user.companyName', 'user.job', 'user.cityName', 'user.phoneValidationCode', 'user.emailValidationCode', 'user.roles', 'user.confirmationToken');
        $memberOfConditions = array();
        foreach ($params['filters'] as $field => $value) {
            if (substr_count($field, '.') > 1) {
                if ($value == 'true' || $value == 'or' || $value == 'and') {
                    list ($entityName, $listName, $listItem) = explode('.', $field);
                    if (!isset($memberOfConditions[$listName])) {
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
                    if (isset($filter_operators[$field]) && $filter_operators[$field] == 'eq') {
                        $qb->andWhere($qb->expr()->eq($field, $qb->expr()->literal($value)));
                    } else {
                        $qb->andWhere($qb->expr()->like($field, $qb->expr()->literal('%' . $value . '%')));
                    }
                } else {
                    $qb->andWhere($field.' = :'.$key.'')->setParameter($key, $value);
                }
            }
        }
        foreach ($memberOfConditions as $listName => $memberOfCondition) {
            if (!empty($memberOfCondition['items'])) {
                if ($memberOfCondition['operator'] == 'or') {
                    $orX = $qb->expr()->orX();
                    foreach ($memberOfCondition['items'] as $i => $item) {
                        $orX->add($qb->expr()->isMemberOf(':'.$listName.'_value_'.$i, 'user.'.$listName));
                        $qb->setParameter($listName.'_value_'.$i, $item);
                    }
                    $qb->andWhere($orX);
                } else if ($memberOfCondition['operator'] == 'and') {
                    $andX = $qb->expr()->andX();
                    foreach ($memberOfCondition['items'] as $i => $item) {
                        $andX->add($qb->expr()->isMemberOf(':'.$listName.'_value_'.$i, 'user.'.$listName));
                        $qb->setParameter($listName.'_value_'.$i, $item);
                    }
                    $qb->andWhere($andX);
                }
            }
        }
        $qbList = clone $qb;
        $qb->select('count(user.id)');
        $data['inlineCount'] = $qb->getQuery()->getSingleScalarResult();
        foreach ($params['order_by'] as $field => $direction) {
            $qbList->addOrderBy($field, $direction);
        }
        $qbList->select('user');
        $qbList->setMaxResults($params['limit']);
        $qbList->setFirstResult($params['offset']);
        $qbList->groupBy('user.id');
        $results = $qbList->getQuery()->getResult();
        if ($results) {
            $data['results'] = $results;
        }
        return $data;
    }

    /**
     * @param array $params
     * @return null|object
     */
    public function get($params = []){
        return $this->findOneBy($params);
    }

    /**
     * @param $entity
     * @param array $params
     * @return User
     */
    public function store($entity, $params= []){
        foreach ($params as $attribut => $value){
            $method = 'set'.lcfirst($attribut);
            $entity->$method($value);
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
    public function update($entity, $params = []){
        foreach ($params as $attribut => $value){
            $method = 'set'.lcfirst($attribut);
            $entity->$method($value);
        }
        $entity = $this->process($entity, false);
        $this->getEntityManager()->flush();
        return $entity;
    }

    /**
     * @param $id
     */
    public function delete($id){
        $entity = $this->find($id);
        $this->getEntityManager()->remove($entity);
        $this->getEntityManager()->flush();
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
?>