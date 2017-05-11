<?php

namespace ContinuousNet\LivnYouBundle\Repository;

use ContinuousNet\LivnYouBundle\Entity\Pathology;
use ContinuousNet\LivnYouBundle\Repository\Join\PathologyJoin;
use Doctrine\ORM\EntityRepository;

/**
 * Class PathologyRepository
 * @package ContinuousNet\LivnYouBundle\Repository
 */
class PathologyRepository extends BaseRepository
{
    protected $serchableFields = ['pathology.name', 'pathology.color'];

    protected $countBy = 'id';

    public function alias()
    {
        return 'pathology';
    }

    public function initRepository()
    {
        $this->pushJoin(PathologyJoin::class);
    }
}
