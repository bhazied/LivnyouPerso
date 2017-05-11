<?php
/**
 * Created by PhpStorm.
 * User: dev03
 * Date: 11/05/17
 * Time: 16:37
 */

namespace ContinuousNet\LivnYouBundle\Repository\Join;

use ContinuousNet\LivnYouBundle\Repository\BaseRepository;

class TranslationPathologyJoin extends BaseJoin
{
    public function apply($queryBuilder)
    {
        $queryBuilder->leftJoin('ContinuousNet\LivnYouBundle\Entity\Pathology', 'pathology', \Doctrine\ORM\Query\Expr\Join::WITH, 'translationPathology.pathology = pathology.id');
        $queryBuilder->leftJoin('ContinuousNet\LivnYouBundle\Entity\User', 'creator_user', \Doctrine\ORM\Query\Expr\Join::WITH, 'translationPathology.creatorUser = creator_user.id');
        $queryBuilder->leftJoin('ContinuousNet\LivnYouBundle\Entity\User', 'modifier_user', \Doctrine\ORM\Query\Expr\Join::WITH, 'translationPathology.modifierUser = modifier_user.id');
        return $queryBuilder;
    }
}
