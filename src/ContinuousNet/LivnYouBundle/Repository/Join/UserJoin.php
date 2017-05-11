<?php
/**
 * Created by PhpStorm.
 * User: dev03
 * Date: 11/05/17
 * Time: 16:38
 */

namespace ContinuousNet\LivnYouBundle\Repository\Join;

use ContinuousNet\LivnYouBundle\Repository\BaseRepository;

class UserJoin extends BaseJoin
{
    public function apply($queryBuilder)
    {
        $queryBuilder->leftJoin('ContinuousNet\LivnYouBundle\Entity\Country', 'country', \Doctrine\ORM\Query\Expr\Join::WITH, 'user.country = country.id');
        $queryBuilder->leftJoin('ContinuousNet\LivnYouBundle\Entity\Language', 'language', \Doctrine\ORM\Query\Expr\Join::WITH, 'user.language = language.id');
        $queryBuilder->leftJoin('ContinuousNet\LivnYouBundle\Entity\User', 'creator_user', \Doctrine\ORM\Query\Expr\Join::WITH, 'user.creatorUser = creator_user.id');
        $queryBuilder->leftJoin('ContinuousNet\LivnYouBundle\Entity\User', 'modifier_user', \Doctrine\ORM\Query\Expr\Join::WITH, 'user.modifierUser = modifier_user.id');
        return $queryBuilder;
    }
}
