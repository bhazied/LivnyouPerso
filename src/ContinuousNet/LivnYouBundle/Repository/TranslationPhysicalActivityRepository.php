<?php

namespace ContinuousNet\LivnYouBundle\Repository;

use ContinuousNet\LivnYouBundle\Repository\Join\TranslationPhysicalActivityJoin;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\PropertyAccess\PropertyAccess;

/**
 * Class TranslationPhysicalActivityRepository
 * @package ContinuousNet\LivnYouBundle\Repository
 */
class TranslationPhysicalActivityRepository extends BaseRepository
{
    protected $serchableFields = ['translationPhysicalActivity.locale', 'translationPhysicalActivity.name', 'translationPhysicalActivity.athleticName'];

    protected $countBy = 'id';

    public function alias()
    {
        return 'translationPhysicalActivity';
    }

    public function initRepository()
    {
        $this->pushJoin(TranslationPhysicalActivityJoin::class);
    }
}
