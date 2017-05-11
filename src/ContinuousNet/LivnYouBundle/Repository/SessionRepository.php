<?php

namespace ContinuousNet\LivnYouBundle\Repository;

use ContinuousNet\LivnYouBundle\Repository\Join\SessionJoin;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\PropertyAccess\PropertyAccess;

/**
 * Class SessionRepository
 * @package ContinuousNet\LivnYouBundle\Repository
 */
class SessionRepository extends BaseRepository
{
    protected $serchableFields = ['session.ip', 'session.userAgent'];

    protected $countBy = 'id';

    public function alias()
    {
        return 'session';
    }
    
    public function initRepository()
    {
        $this->pushJoin(SessionJoin::class);
    }
}
