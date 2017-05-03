<?php

namespace ContinuousNet\LivnYouBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Symfony\Component\PropertyAccess\PropertyAccess;

/**
 * Class GroupRepository
 * @package ContinuousNet\LivnYouBundle\Repository
 */
class GroupRepository extends EntityRepository implements IRepository
{
    public function getAll($params = [])
    {
        $qBuilder = $this->createQueryBuilder('group_');
        $qBuilder->leftJoin('ContinuousNet\LivnYouBundle\Entity\User', 'creator_user', \Doctrine\ORM\Query\Expr\Join::WITH, 'group_.creatorUser = creator_user.id');
        $qBuilder->leftJoin('ContinuousNet\LivnYouBundle\Entity\User', 'modifier_user', \Doctrine\ORM\Query\Expr\Join::WITH, 'group_.modifierUser = modifier_user.id');
        $textFields = array('group_.name', 'group_.roles');
        $memberOfConditions = array();
        foreach ($params['filters'] as $field => $value) {
            if (substr_count($field, '.') > 1) {
                if ($value == 'true' || $value == 'or' || $value == 'and') {
                    list($entityName, $listName, $listItem) = explode('.', $field);
                    $entityName = null;
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
                    if (isset($filterOperators[$field]) && $filterOperators[$field] == 'eq') {
                        $qBuilder->andWhere($qb->expr()->eq($field, $qb->expr()->literal($value)));
                    } else {
                        $qBuilder->andWhere($qb->expr()->like($field, $qb->expr()->literal('%' . $value . '%')));
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
                        $orX->add($qBuilder->expr()->isMemberOf(':'.$listName.'_value_'.$i, 'group_.'.$listName));
                        $qBuilder->setParameter($listName.'_value_'.$i, $item);
                    }
                    $qBuilder->andWhere($orX);
                } elseif ($memberOfCondition['operator'] == 'and') {
                    $andX = $qBuilder->expr()->andX();
                    foreach ($memberOfCondition['items'] as $i => $item) {
                        $andX->add($qb->expr()->isMemberOf(':'.$listName.'_value_'.$i, 'group_.'.$listName));
                        $qBuilder->setParameter($listName.'_value_'.$i, $item);
                    }
                    $qBuilder->andWhere($andX);
                }
            }
        }
        $qbList = clone $qBuilder;
        $qb->select('count(group_.id)');
        $data['inlineCount'] = $qb->getQuery()->getSingleScalarResult();
        foreach ($params['order_by'] as $field => $direction) {
            $qbList->addOrderBy($field, $direction);
        }
        $qbList->select('group_');
        $qbList->setMaxResults($params['limit']);
        $qbList->setFirstResult($params['offset']);
        $qbList->groupBy('group_.id');
        $results = $qbList->getQuery()->getResult();
        $data['results'] = $results;
        return $data;
    }

    public function get($params = [])
    {
        return $this->findOneBy($params);
    }

    public function store($entity, $params= [])
    {
        $accessor = PropertyAccess::createPropertyAccessor();
        foreach ($params as $attribut => $value) {
            $accessor->setValue($entity, $attribut, $value);
        }
        $this->getEntityManager()->persist($entity);
        $this->getEntityManager()->flush();
        return $entity;
    }

    public function update($entity, $params = [])
    {
        $accessor = PropertyAccess::createPropertyAccessor();
        foreach ($params as $attribut => $value) {
            $accessor->setValue($entity, $attribut, $value);
        }
        $this->getEntityManager()->flush();
        return $entity;
    }

    public function delete($idEntity)
    {
        $entity = $this->find($idEntity);
        $this->getEntityManager()->remove($entity);
        $this->getEntityManager()->flush();
    }

    public function count($params = [])
    {
        $qBuilder = $this->createQueryBuilder('group_');
        if (array_key_exists('filters', $params)) {
            foreach ($params['filters'] as $fKey => $value) {
                $qBuilder->andWhere('group_.'.$fKey .'=:'.$fKey)->setParameter($fKey, $value);
            }
        }
        $qBuilder->select('count(group_.id)');
        return $qBuilder->getQuery()->getSingleScalarResult();
        ;
    }
}
