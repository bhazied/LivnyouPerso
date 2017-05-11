<?php

namespace ContinuousNet\LivnYouBundle\Repository;

use ContinuousNet\LivnYouBundle\Repository\Join\TranslationPathologyJoin;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\PropertyAccess\PropertyAccess;

/**
 * Class TranslationPathologyRepository
 * @package ContinuousNet\LivnYouBundle\Repository
 */
class TranslationPathologyRepository extends BaseRepository
{
    protected $serchableFields = ['translationPathology.locale', 'translationPathology.name'];

    protected $countBy = 'id';

    public function alias()
    {
        return 'translationPathology';
    }

    public function initRepository()
    {
        $this->pushJoin(TranslationPathologyJoin::class);
    }
}
