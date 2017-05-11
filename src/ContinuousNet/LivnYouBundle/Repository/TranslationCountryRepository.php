<?php

namespace ContinuousNet\LivnYouBundle\Repository;

use ContinuousNet\LivnYouBundle\Repository\Join\TranslationCountryJoin;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\PropertyAccess\PropertyAccess;

/**
 * Class TranslationCountryRepository
 * @package ContinuousNet\LivnYouBundle\Repository
 */
class TranslationCountryRepository extends BaseRepository
{
    protected $serchableFields = ['translationCountry.locale', 'translationCountry.name'];

    protected $countBy = 'id';

    public function alias()
    {
        return 'translationCountry';
    }

    public function initRepository()
    {
        $this->pushJoin(TranslationCountryJoin::class);
    }
}
