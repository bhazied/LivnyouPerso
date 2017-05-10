<?php

namespace ContinuousNet\LivnYouBundle\Repository;

use ContinuousNet\LivnYouBundle\Repository\Join\GroupJoin;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\PropertyAccess\PropertyAccess;

/**
 * Class GroupRepository
 * @package ContinuousNet\LivnYouBundle\Repository
 */
class GroupRepository extends BaseRepository
{
    protected $serchableFields = ['group_.name', 'group_.roles'];

    protected $countBy = 'id';

    public function alias()
    {
        return 'group_';
    }

    public function initRepository()
    {
        $this->pushJoin(GroupJoin::class);
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
    }
}
