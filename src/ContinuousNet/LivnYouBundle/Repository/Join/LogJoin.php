<?php
/**
 * Created by PhpStorm.
 * User: dev03
 * Date: 10/05/17
 * Time: 18:44
 */

namespace ContinuousNet\LivnYouBundle\Repository\Join;

use ContinuousNet\LivnYouBundle\Repository\BaseRepository;

class LogJoin extends BaseJoin
{
    public function apply($queryBuilder, BaseRepository $repository)
    {
        $queryBuilder->leftJoin('ContinuousNet\LivnYouBundle\Entity\Session', 'session', \Doctrine\ORM\Query\Expr\Join::WITH, 'log.session = session.id');
        $queryBuilder->leftJoin('ContinuousNet\LivnYouBundle\Entity\User', 'creator_user', \Doctrine\ORM\Query\Expr\Join::WITH, 'log.creatorUser = creator_user.id');
        return $queryBuilder;
    }
}
