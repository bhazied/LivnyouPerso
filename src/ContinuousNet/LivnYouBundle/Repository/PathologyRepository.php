<?php

namespace ContinuousNet\LivnYouBundle\Repository;

use Doctrine\ORM\EntityRepository;

/**
 * Class PathologyRepository
 * @package ContinuousNet\LivnYouBundle\Repository
 */
class PathologyRepository extends BaseRepository
{
    public function alias()
    {
        return 'pathology';
    }


    public function getAll($params = [])
    {
        $qBuilder = $this->createQueryBuilder('pathology');
        $qBuilder->leftJoin('ContinuousNet\LivnYouBundle\Entity\User', 'creator_user', \Doctrine\ORM\Query\Expr\Join::WITH, 'pathology.creatorUser = creator_user.id');
        $qBuilder->leftJoin('ContinuousNet\LivnYouBundle\Entity\User', 'modifier_user', \Doctrine\ORM\Query\Expr\Join::WITH, 'pathology.modifierUser = modifier_user.id');
        $textFields = array('pathology.name', 'pathology.color');
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
                    if (isset($params['filterOperators'][$field]) && $params['filter_operators'][$field] == 'eq') {
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
                        $orX->add($qBuilder->expr()->isMemberOf(':'.$listName.'_value_'.$i, 'pathology.'.$listName));
                        $qBuilder->setParameter($listName.'_value_'.$i, $item);
                    }
                    $qBuilder->andWhere($orX);
                } elseif ($memberOfCondition['operator'] == 'and') {
                    $andX = $qBuilder->expr()->andX();
                    foreach ($memberOfCondition['items'] as $i => $item) {
                        $andX->add($qBuilder->expr()->isMemberOf(':'.$listName.'_value_'.$i, 'pathology.'.$listName));
                        $qBuilder->setParameter($listName.'_value_'.$i, $item);
                    }
                    $qBuilder->andWhere($andX);
                }
            }
        }
       
        $qbList = clone $qBuilder;
        $qBuilder->select('count(pathology.id)');
        $data['inlineCount'] = $qBuilder->getQuery()->getSingleScalarResult();
        foreach ($params['orderBy'] as $field => $direction) {
            $qbList->addOrderBy($field, $direction);
        }
        $qbList->select('pathology');
        $qbList->setMaxResults($params['limit']);
        $qbList->setFirstResult($params['offset']);
        $qbList->groupBy('pathology.id');
        $results = $qbList->getQuery()->getResult();
        $data['results'] = $results;
        return $data;
    }
}
