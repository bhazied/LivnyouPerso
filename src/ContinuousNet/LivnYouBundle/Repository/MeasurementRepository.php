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

    public function delete($id)
    {
        $entity = $this->find($id);
        $this->getEntityManager()->remove($entity);
        $this->getEntityManager()->flush();
    }

    public function count($params = [])
    {
        $qb = $this->createQueryBuilder('measurement');
        if (array_key_exists('filters', $params)) {
            foreach ($params['filters'] as $fKey => $value) {
                $qb->andWhere('measurement.'.$fKey .'=:'.$fKey)->setParameter($fKey, $value);
            }
        }
        $qb->select('count(measurement.id)');
        return $qb->getQuery()->getSingleScalarResult();
        ;
    }
}
