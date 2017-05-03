<?php

namespace ContinuousNet\LivnYouBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Symfony\Component\PropertyAccess\PropertyAccess;

/**
 * Class MeasurementRepository
 * @package ContinuousNet\LivnYouBundle\Repository
 */
class MeasurementRepository extends EntityRepository implements IRepository
{
    public function getAll($params = [])
    {
        $qBuilder = $this->createQueryBuilder('measurement');
        $qBuilder->leftJoin('ContinuousNet\LivnYouBundle\Entity\Country', 'country', \Doctrine\ORM\Query\Expr\Join::WITH, 'measurement.country = country.id');
        $qBuilder->leftJoin('ContinuousNet\LivnYouBundle\Entity\PhysicalActivity', 'physical_activity', \Doctrine\ORM\Query\Expr\Join::WITH, 'measurement.physicalActivity = physical_activity.id');
        $qBuilder->leftJoin('ContinuousNet\LivnYouBundle\Entity\User', 'creator_user', \Doctrine\ORM\Query\Expr\Join::WITH, 'measurement.creatorUser = creator_user.id');
        $qBuilder->leftJoin('ContinuousNet\LivnYouBundle\Entity\User', 'modifier_user', \Doctrine\ORM\Query\Expr\Join::WITH, 'measurement.modifierUser = modifier_user.id');
        $textFields = array('measurement.firstName', 'measurement.lastName', 'measurement.groupName', 'measurement.address', 'measurement.city', 'measurement.zipCode', 'measurement.state', 'measurement.mobileNumber', 'measurement.email', 'measurement.phone', 'measurement.appName', 'measurement.appVersion', 'measurement.dataReceived', 'measurement.fmHcPcZaMaxColor', 'measurement.fmHcPcZbMaxColor', 'measurement.fmHcPcZcMaxColor', 'measurement.fmHcPcZdMaxColor', 'measurement.fmHcPcZeMaxColor', 'measurement.fmHcPcZfMaxColor', 'measurement.ffwPcZaMaxColor', 'measurement.ffwPcZbMaxColor', 'measurement.ffwPcZcMaxColor', 'measurement.ffwPcZdMaxColor', 'measurement.ffwPcZeMaxColor', 'measurement.ffwPcZfMaxColor', 'measurement.ffwPcZgMaxColor', 'measurement.mmhiZaMaxColor', 'measurement.mmhiZbMaxColor', 'measurement.mmhiZcMaxColor', 'measurement.mmhiZdMaxColor', 'measurement.adcrZaMaxColor', 'measurement.adcrZbMaxColor', 'measurement.adcrZcMaxColor', 'measurement.adcrZdMaxColor', 'measurement.adcrZeMaxColor', 'measurement.asmmiZaMaxColor', 'measurement.asmmiZbMaxColor', 'measurement.asmmiZcMaxColor', 'measurement.asmmiZdMaxColor', 'measurement.ecwPcZaMaxColor', 'measurement.ecwPcZbMaxColor', 'measurement.ecwPcZcMaxColor', 'measurement.ecwPcZdMaxColor', 'measurement.ecwPcZeMaxColor', 'measurement.ecwPcZfMaxColor', 'measurement.ecwPcZgMaxColor', 'measurement.icwPcZaMaxColor', 'measurement.icwPcZbMaxColor', 'measurement.icwPcZcMaxColor', 'measurement.icwPcZdMaxColor', 'measurement.icwPcZeMaxColor', 'measurement.icwPcZfMaxColor', 'measurement.icwPcZgMaxColor', 'measurement.fmPcZaMaxColor', 'measurement.fmPcZbMaxColor', 'measurement.fmPcZcMaxColor', 'measurement.fmPcZdMaxColor', 'measurement.fmPcZeMaxColor', 'measurement.fmPcZfMaxColor', 'measurement.tbwffmPcZaMaxColor', 'measurement.tbwffmPcZbMaxColor', 'measurement.tbwffmPcZcMaxColor', 'measurement.tbwffmPcZdMaxColor', 'measurement.tbwffmPcZeMaxColor', 'measurement.tbwffmPcZfMaxColor', 'measurement.tbwffmPcZgMaxColor', 'measurement.dffmiZaMaxColor', 'measurement.dffmiZbMaxColor', 'measurement.dffmiZcMaxColor', 'measurement.dffmiZdMaxColor', 'measurement.mpMetaiZaMaxColor', 'measurement.mpMetaiZbMaxColor', 'measurement.mpMetaiZcMaxColor', 'measurement.mpMetaiZdMaxColor', 'measurement.iffmiZaMaxColor', 'measurement.iffmiZbMaxColor', 'measurement.iffmiZcMaxColor', 'measurement.iffmiZdMaxColor', 'measurement.bmriZaMaxColor', 'measurement.bmriZbMaxColor', 'measurement.bmriZcMaxColor', 'measurement.bmriZdMaxColor', 'measurement.ffecwPcZaMaxColor', 'measurement.ffecwPcZbMaxColor', 'measurement.ffecwPcZcMaxColor', 'measurement.ffecwPcZdMaxColor', 'measurement.ffecwPcZeMaxColor', 'measurement.ffecwPcZfMaxColor', 'measurement.ffecwPcZgMaxColor', 'measurement.fficwPcZaMaxColor', 'measurement.fficwPcZbMaxColor', 'measurement.fficwPcZcMaxColor', 'measurement.fficwPcZdMaxColor', 'measurement.fficwPcZeMaxColor', 'measurement.fficwPcZfMaxColor', 'measurement.fficwPcZgMaxColor', 'measurement.asmhiZaMaxColor', 'measurement.asmhiZbMaxColor', 'measurement.asmhiZcMaxColor', 'measurement.asmhiZdMaxColor', 'measurement.bcmiZaMaxColor', 'measurement.bcmiZbMaxColor', 'measurement.bcmiZcMaxColor', 'measurement.bcmiZdMaxColor', 'measurement.imcZaMaxColor', 'measurement.imcZbMaxColor', 'measurement.imcZcMaxColor', 'measurement.imcZdMaxColor', 'measurement.imcZeMaxColor', 'measurement.imcZfMaxColor', 'measurement.imcZgMaxColor', 'measurement.fmslmirZaMaxColor', 'measurement.fmslmirZbMaxColor', 'measurement.fmirZaMaxColor', 'measurement.fmirZbMaxColor', 'measurement.slmirZaMaxColor', 'measurement.slmirZbMaxColor', 'measurement.whrZaMaxColor', 'measurement.whrZbMaxColor', 'measurement.whtrZaMaxColor', 'measurement.whtrZbMaxColor', 'measurement.totalCcScZaMaxColor', 'measurement.totalCcScZbMaxColor', 'measurement.totalCcScZcMaxColor', 'measurement.totalMuhScZaMaxColor', 'measurement.totalMuhScZbMaxColor', 'measurement.totalMuhScZcMaxColor', 'measurement.cibleZaColor', 'measurement.cibleZbColor', 'measurement.cibleZcColor', 'measurement.cibleZdColor', 'measurement.cibleZeColor', 'measurement.cibleZfColor', 'measurement.asmliColor', 'measurement.asmtliColor', 'measurement.request', 'measurement.response', 'measurement.biodyBluetoothMacAddress', 'measurement.machineBluetoothMacAddress');
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
                        $orX->add($qBuilder->expr()->isMemberOf(':'.$listName.'_value_'.$i, 'measurement.'.$listName));
                        $qBuilder->setParameter($listName.'_value_'.$i, $item);
                    }
                    $qBuilder->andWhere($orX);
                } elseif ($memberOfCondition['operator'] == 'and') {
                    $andX = $qBuilder->expr()->andX();
                    foreach ($memberOfCondition['items'] as $i => $item) {
                        $andX->add($qBuilder->expr()->isMemberOf(':'.$listName.'_value_'.$i, 'measurement.'.$listName));
                        $qBuilder->setParameter($listName.'_value_'.$i, $item);
                    }
                    $qBuilder->andWhere($andX);
                }
            }
        }
        $qbList = clone $qBuilder;
        $qBuilder->select('count(measurement.id)');
        $data['inlineCount'] = $qBuilder->getQuery()->getSingleScalarResult();
        foreach ($params['orderBy'] as $field => $direction) {
            $qbList->addOrderBy($field, $direction);
        }
        $qbList->select('measurement');
        $qbList->setMaxResults($params['limit']);
        $qbList->setFirstResult($params['offset']);
        $qbList->groupBy('measurement.id');
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
        $qbBuilder = $this->createQueryBuilder('measurement');
        if (array_key_exists('filters', $params)) {
            foreach ($params['filters'] as $fKey => $value) {
                $qbBuilder->andWhere('measurement.'.$fKey .'=:'.$fKey)->setParameter($fKey, $value);
            }
        }
        $qbBuilder->select('count(measurement.id)');
        return $qbBuilder->getQuery()->getSingleScalarResult();
    }
}
