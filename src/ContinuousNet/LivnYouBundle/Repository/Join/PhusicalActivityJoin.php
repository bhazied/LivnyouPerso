<?php
/**
 * Created by PhpStorm.
 * User: dev03
 * Date: 10/05/17
 * Time: 18:54
 */

namespace ContinuousNet\LivnYouBundle\Repository\Join;

use ContinuousNet\LivnYouBundle\Repository\BaseRepository;

class PhusicalActivityJoin extends BaseJoin
{
    public function apply($queryBuilder, BaseRepository $repository)
    {
        $queryBuilder->leftJoin('ContinuousNet\LivnYouBundle\Entity\User', 'creator_user', \Doctrine\ORM\Query\Expr\Join::WITH, 'physicalActivity.creatorUser = creator_user.id');
        $queryBuilder->leftJoin('ContinuousNet\LivnYouBundle\Entity\User', 'modifier_user', \Doctrine\ORM\Query\Expr\Join::WITH, 'physicalActivity.modifierUser = modifier_user.id');
        return $queryBuilder;
    }
}
