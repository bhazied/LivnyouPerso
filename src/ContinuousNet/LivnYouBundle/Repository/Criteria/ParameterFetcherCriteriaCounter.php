<?php
/**
 * Created by PhpStorm.
 * User: dev03
 * Date: 10/05/17
 * Time: 19:03
 */

namespace ContinuousNet\LivnYouBundle\Repository\Criteria;

use ContinuousNet\LivnYouBundle\Repository\BaseRepository;
use FOS\RestBundle\Request\ParamFetcherInterface;

class ParameterFetcherCriteriaCounter extends BaseCriteria
{
    protected $paramFetcher;

    public function __construct(ParamFetcherInterface $paramFetcher)
    {
        $this->paramFetcher = $paramFetcher;
    }

    public function apply($qBuilder, BaseRepository $repository)
    {
        $seachableFields = $repository->getSeachableFields();
        $filterOperators = $this->paramFetcher->get('filter_operators') ? $this->paramFetcher->get('filter_operators') : array();
        $filters = !is_null($this->paramFetcher->get('filters')) ? $this->paramFetcher->get('filters') : array();
        $memberOfConditions = array();
        foreach ($filters as $field => $value) {
            if (substr_count($field, '.') > 1) {
                if ($value == 'true' || $value == 'or' || $value == 'and') {
                    list($entityName, $listName, $listItem) = explode('.', $field);
                    $entityName = null;
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
                if (in_array($field, $seachableFields)) {
                    if (isset($filterOperators[$field]) && $filterOperators[$field] == 'eq') {
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
                        $orX->add($qBuilder->expr()->isMemberOf(':'.$listName.'_value_'.$i, 'country.'.$listName));
                        $qBuilder->setParameter($listName.'_value_'.$i, $item);
                    }
                    $qBuilder->andWhere($orX);
                } elseif ($memberOfCondition['operator'] == 'and') {
                    $andX = $qBuilder->expr()->andX();
                    foreach ($memberOfCondition['items'] as $i => $item) {
                        $andX->add($qBuilder->expr()->isMemberOf(':'.$listName.'_value_'.$i, 'country.'.$listName));
                        $qBuilder->setParameter($listName.'_value_'.$i, $item);
                    }
                    $qBuilder->andWhere($andX);
                }
            }
        }
        $qBuilder->select('count('.$repository->alias().'.'.$repository->getCountby().')');
        return $qBuilder;
    }
}
