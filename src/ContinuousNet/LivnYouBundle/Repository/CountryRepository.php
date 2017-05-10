<?php

namespace ContinuousNet\LivnYouBundle\Repository;

use ContinuousNet\LivnYouBundle\Repository\Join\CountryJoin;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\PropertyAccess\PropertyAccess;

/**
 * Class CountryRepository
 * @package ContinuousNet\LivnYouBundle\Repository
 */
class CountryRepository extends BaseRepository
{
    protected $serchableFields = ['country.name', 'country.picture', 'country.code', 'country.longCode', 'country.prefix'];

    protected $countBy = 'id';

    public function alias()
    {
        return 'country';
    }

    public function initRepository()
    {
        $this->pushJoin(CountryJoin::class);
    }
}
