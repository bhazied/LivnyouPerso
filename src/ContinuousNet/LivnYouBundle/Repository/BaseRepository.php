<?php
/**
 * Created by PhpStorm.
 * User: dev03
 * Date: 02/05/17
 * Time: 11:29
 */

namespace ContinuousNet\LivnYouBundle\Repository;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\PropertyAccess\PropertyAccess;

abstract class BaseRepository extends EntityRepository implements IRepository
{
    /* whene i get the time i will redefine and impliment contract*/

    protected $queryBuilder;

    public function __construct(EntityManager $entityManager, \Doctrine\ORM\Mapping\ClassMetadata $class)
    {
        parent::__construct($entityManager, $class);
        $this->makeQueryBuilder();
    }

    abstract public function alias();

    public function makeQueryBuilder()
    {
        $queryBuilder = $this->createQueryBuilder($this->alias());
        return $this->queryBuilder = $queryBuilder;
    }

    public function getAll($params = [])
    {
        // TODO: Implement getAll() method.
    }

    public function get($params = [])
    {
        return $this->findOneBy($params);
    }

    public function store($entity, $params = [])
    {
        $accessor = PropertyAccess::createPropertyAccessor();
        foreach ($params as $attribut => $value) {
            $accessor->setValue($entity, $attribut, $value);
        }
        $this->_em->persist($entity);
        $this->_em->flush();
        return $entity;
    }

    public function update($entity, $params = [])
    {
        $accessor = PropertyAccess::createPropertyAccessor();
        foreach ($params as $attribut => $value) {
            $accessor->setValue($entity, $attribut, $value);
        }
        $this->_em->flush();
        return $entity;
    }

    public function delete($idEntity)
    {
        $entity = $this->find($idEntity);
        $this->_em->remove($entity);
        $this->_em->flush();
    }
}
