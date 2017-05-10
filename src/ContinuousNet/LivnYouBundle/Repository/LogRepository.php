<?php

namespace ContinuousNet\LivnYouBundle\Repository;

use ContinuousNet\LivnYouBundle\Repository\Join\LogJoin;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\PropertyAccess\PropertyAccess;

/**
 * Class LogRepository
 * @package ContinuousNet\LivnYouBundle\Repository
 */
class LogRepository extends BaseRepository
{
    protected $serchableFields = ['log.url', 'log.method', 'log.note', 'log.ipAddress', 'log.userAgent', 'log.application'];

    protected $countBy = 'id';

    public function alias()
    {
        return 'log';
    }

    public function initRepository()
    {
        $this->pushCriteria(LogJoin::class);
    }
}
