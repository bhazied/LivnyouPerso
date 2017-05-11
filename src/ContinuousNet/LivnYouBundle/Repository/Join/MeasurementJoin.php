<?php
/**
 * Created by PhpStorm.
 * User: dev03
 * Date: 11/05/17
 * Time: 17:19
 */

namespace ContinuousNet\LivnYouBundle\Repository\Join;

use ContinuousNet\LivnYouBundle\Repository\BaseRepository;

class MeasurementJoin extends BaseJoin
{
    public function apply($queryBuilder)
    {
        $queryBuilder->leftJoin('ContinuousNet\LivnYouBundle\Entity\Country', 'country', \Doctrine\ORM\Query\Expr\Join::WITH, 'measurement.country = country.id');
        $queryBuilder->leftJoin('ContinuousNet\LivnYouBundle\Entity\PhysicalActivity', 'physical_activity', \Doctrine\ORM\Query\Expr\Join::WITH, 'measurement.physicalActivity = physical_activity.id');
        $queryBuilder->leftJoin('ContinuousNet\LivnYouBundle\Entity\User', 'creator_user', \Doctrine\ORM\Query\Expr\Join::WITH, 'measurement.creatorUser = creator_user.id');
        $queryBuilder->leftJoin('ContinuousNet\LivnYouBundle\Entity\User', 'modifier_user', \Doctrine\ORM\Query\Expr\Join::WITH, 'measurement.modifierUser = modifier_user.id');
        return $queryBuilder;
    }
}
