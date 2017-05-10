<?php

namespace ContinuousNet\LivnYouBundle\Repository;

use ContinuousNet\LivnYouBundle\Repository\Join\PhusicalActivityJoin;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\PropertyAccess\PropertyAccess;

/**
 * Class PhysicalActivityRepository
 * @package ContinuousNet\LivnYouBundle\Repository
 */
class PhysicalActivityRepository extends BaseRepository
{
    protected $serchableFields = ['physicalActivity.name', 'physicalActivity.athleticName'];
    
    protected $countBy = 'id';
    
    public function alias()
    {
        return 'physicalActivity';
    }

    public function initRepository()
    {
        $this->pushJoin(PhusicalActivityJoin::class);
    }
}
