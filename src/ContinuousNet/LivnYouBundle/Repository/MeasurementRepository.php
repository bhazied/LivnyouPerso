<?php

namespace ContinuousNet\LivnYouBundle\Repository;

use Doctrine\ORM\EntityRepository;

/**
 * Class MeasurementRepository
 * @package ContinuousNet\LivnYouBundle\Repository
 */
class MeasurementRepository extends EntityRepository implements IRepository{


    public function getAll($params = []){

    }

    public function get($params = []){
        return $this->findOneBy($params);
    }

    public function store($entity, $params= []){
        foreach ($params as $attribut => $value){
            $method = 'set'.lcfirst($attribut);
            $entity->$method($value);
        }
        $this->getEntityManager()->persist($entity);
        $this->getEntityManager()->flush();
        return $entity;
    }

    public function update($entity, $params = []){
        foreach ($params as $attribut => $value){
            $method = 'set'.lcfirst($attribut);
            $entity->$method($value);
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