<?php

namespace ContinuousNet\LivnYouBundle\Repository;

use ContinuousNet\LivnYouBundle\Repository\Join\LanguageJoin;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\PropertyAccess\PropertyAccess;

/**
 * Class LanguageRepository
 * @package ContinuousNet\LivnYouBundle\Repository
 */
class LanguageRepository extends BaseRepository
{
    protected $serchableFields = ['language.name', 'language.locale', 'language.code'];

    protected $countBy = 'id';

    public function alias()
    {
        return 'language';
    }

    public function initRepository()
    {
        $this->pushJoin(LanguageJoin::class);
    }
}
