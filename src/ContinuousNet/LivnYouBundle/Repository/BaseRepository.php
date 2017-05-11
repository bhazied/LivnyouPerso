<?php
/**
 * Created by PhpStorm.
 * User: dev03
 * Date: 02/05/17
 * Time: 11:29
 */

namespace ContinuousNet\LivnYouBundle\Repository;

use ContinuousNet\LivnYouBundle\Repository\Criteria\BaseCriteria;
use ContinuousNet\LivnYouBundle\Repository\Criteria\IRepositoryCriteria;
use ContinuousNet\LivnYouBundle\Repository\Join\BaseJoin;
use ContinuousNet\LivnYouBundle\Repository\Join\IRepositoryJoin;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\NoResultException;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\PropertyAccess\PropertyAccess;

abstract class BaseRepository extends EntityRepository implements IRepository, IRepositoryCriteria, IRepositoryJoin
{
    /* whene i get the time i will redefine and impliment contract*/

    protected $container;

    protected $queryBuilder;

    /**
     * @var \ArrayObject
     */
    protected $criteria;

    /**
     * @var \ArrayObject
     */
    protected $join;

    protected $skipCriteria = false;

    protected $skipJoin = false;

    protected $serchableFields = [];

    protected $countBy;

    public function __construct(EntityManager $entityManager, \Doctrine\ORM\Mapping\ClassMetadata $class)
    {
        parent::__construct($entityManager, $class);

        $this->container = new ContainerBuilder();

        $this->makeQueryBuilder();

        $this->resetCriteria();

        $this->resetJoin();

        $this->initRepository();
    }

    abstract public function alias();

    public function makeQueryBuilder()
    {
        $queryBuilder = $this->createQueryBuilder($this->alias());
        return $this->queryBuilder = $queryBuilder;
    }

    public function initRepository()
    {
    }

    public function getSeachableFields()
    {
        return $this->serchableFields;
    }

    public function getCountby()
    {
        return $this->countBy;
    }

    public function getByCriteria(BaseCriteria $criteria)
    {
        $this->queryBuilder = $criteria->apply($this->queryBuilder, $this);
        $result = $this->queryBuilder->getQuery()->getResult();
        return $result;
    }

    public function pushCriteria($criteria)
    {
        if (is_string($criteria)) {
            $criteria = new $criteria();
        }
        if (! $criteria instanceof BaseCriteria) {
            throw  new Exception('the class '. get_class($criteria) . 'must be instance of Repository\Criteria\BaseCriteria');
        }
        $this->criteria->append($criteria);
        return $this;
    }

    public function getCriteria()
    {
        return $this->criteria;
    }

    public function skipCriteria($status = true)
    {
        $this->skipCriteria = $status;
        return $this;
    }

    public function applyCriteria()
    {
        if ($this->skipCriteria === true) {
            return $this;
        }
        $criteria = $this->getCriteria();
        if ($criteria) {
            foreach ($criteria as $condition) {
                $this->queryBuilder = $condition->apply($this->queryBuilder, $this);
            }
        }
        return $this;
    }

    public function resetCriteria()
    {
        $this->criteria = new \ArrayObject();
        return $this;
    }

    public function pushJoin($join)
    {
        if (is_string($join)) {
            $join = new $join();
        }
        if (! $join instanceof BaseJoin) {
            throw  new Exception('the class '. get_class($join) . 'must be instance of Repository\Join\BaseJoin');
        }
        $this->join->append($join);
        return $this;
    }

    public function getJoin()
    {
        return $this->join;
    }

    public function resetJoin()
    {
        $this->join = new \ArrayObject();
        return $this;
    }

    public function applyJoin()
    {
        if ($this->join === true) {
            return $this;
        }
        $join = $this->getJoin();
        if ($join) {
            foreach ($join as $joined) {
                $this->queryBuilder = $joined->apply($this->queryBuilder);
            }
        }
        return $this;
    }

    public function skipJoin($status = false)
    {
        $this->skipCriteria = $status;
        return false;
    }


    public function getAll()
    {
        $this->applyCriteria();
        $this->queryBuilder->select($this->alias());
        return $this->queryBuilder->getQuery()->getResult();
    }

    public function countAll()
    {
        $this->applyCriteria();
        try {
            $this->queryBuilder->select('count('.$this->alias().'.'.$this->getCountby().')');
            return $this->queryBuilder->getQuery()->getSingleScalarResult();
        } catch (NoResultException $exception) {
            return 0;
        }
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
