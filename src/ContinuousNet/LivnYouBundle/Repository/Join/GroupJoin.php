<?php
/**
 * Created by PhpStorm.
 * User: dev03
 * Date: 10/05/17
 * Time: 18:11
 */

namespace ContinuousNet\LivnYouBundle\Repository\Join;

use ContinuousNet\LivnYouBundle\Repository\BaseRepository;

class GroupJoin extends BaseJoin
{
    public function apply($queryBuilder)
    {
        $queryBuilder->leftJoin('ContinuousNet\LivnYouBundle\Entity\User', 'creator_user', \Doctrine\ORM\Query\Expr\Join::WITH, 'group_.creatorUser = creator_user.id');
        $queryBuilder->leftJoin('ContinuousNet\LivnYouBundle\Entity\User', 'modifier_user', \Doctrine\ORM\Query\Expr\Join::WITH, 'group_.modifierUser = modifier_user.id');
        return $queryBuilder;
    }
}
