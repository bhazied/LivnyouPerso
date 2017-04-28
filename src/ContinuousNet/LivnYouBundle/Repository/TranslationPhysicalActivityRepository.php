<?php

namespace ContinuousNet\LivnYouBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Symfony\Component\PropertyAccess\PropertyAccess;

/**
 * Class TranslationPhysicalActivityRepository
 * @package ContinuousNet\LivnYouBundle\Repository
 */
class TranslationPhysicalActivityRepository extends EntityRepository implements IRepository{


    public function getAll($params = []){

        $qb = $this->createQueryBuilder('translationPhysicalActivity');
        $qb->leftJoin('ContinuousNet\LivnYouBundle\Entity\PhysicalActivity', 'physical_activity', \Doctrine\ORM\Query\Expr\Join::WITH, 'translationPhysicalActivity.physicalActivity = physical_activity.id');
        $qb->leftJoin('ContinuousNet\LivnYouBundle\Entity\User', 'creator_user', \Doctrine\ORM\Query\Expr\Join::WITH, 'translationPhysicalActivity.creatorUser = creator_user.id');
        $qb->leftJoin('ContinuousNet\LivnYouBundle\Entity\User', 'modifier_user', \Doctrine\ORM\Query\Expr\Join::WITH, 'translationPhysicalActivity.modifierUser = modifier_user.id');
        $textFields = array('translationPhysicalActivity.locale', 'translationPhysicalActivity.name', 'translationPhysicalActivity.athleticName');
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
                        $orX->add($qb->expr()->isMemberOf(':'.$listName.'_value_'.$i, 'translationPhysicalActivity.'.$listName));
                        $qb->setParameter($listName.'_value_'.$i, $item);
                    }
                    $qb->andWhere($orX);
                } else if ($memberOfCondition['operator'] == 'and') {
                    $andX = $qb->expr()->andX();
                    foreach ($memberOfCondition['items'] as $i => $item) {
                        $andX->add($qb->expr()->isMemberOf(':'.$listName.'_value_'.$i, 'translationPhysicalActivity.'.$listName));
                        $qb->setParameter($listName.'_value_'.$i, $item);
                    }
                    $qb->andWhere($andX);
                }
            }
        }
        $qbList = clone $qb;
        $qb->select('count(translationPhysicalActivity.id)');
        $data['inlineCount'] = $qb->getQuery()->getSingleScalarResult();
        foreach ($params['order_by'] as $field => $direction) {
            $qbList->addOrderBy($field, $direction);
        }
        $qbList->select('translationPhysicalActivity');
        $qbList->setMaxResults($params['limit']);
        $qbList->setFirstResult($params['offset']);
        $qbList->groupBy('translationPhysicalActivity.id');
        $results = $qbList->getQuery()->getResult();
        if ($results) {
            $data['results'] = $results;
        }
        return $data;
    }

    public function get($params = []){
        return $this->findOneBy($params);
    }

    public function store($entity, $params= []){
        $accessor = PropertyAccess::createPropertyAccessor();
        foreach ($params as $attribut => $value){
            $accessor->setValue($entity, $attribut, $value);
        }
        $this->getEntityManager()->persist($entity);
        $this->getEntityManager()->flush();
        return $entity;
    }

    public function update($entity, $params = []){
        $accessor = PropertyAccess::createPropertyAccessor();
        foreach ($params as $attribut => $value){
            $accessor->setValue($entity, $attribut, $value);
        }
        $this->getEntityManager()->flush();
        return $entity;
    }

    public function delete($id){
        $entity = $this->find($id);
        $this->getEntityManager()->remove($entity);
        $this->getEntityManager()->flush();
    }
}
?>