<?php

namespace ContinuousNet\LivnYouBundle\Repository;

use ContinuousNet\LivnYouBundle\Repository\Join\TemplateJoin;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\PropertyAccess\PropertyAccess;

/**
 * Class TemplateRepository
 * @package ContinuousNet\LivnYouBundle\Repository
 */
class TemplateRepository extends BaseRepository
{
    protected $serchableFields = ['template.name'];

    protected $countBy = 'id';

    public function alias()
    {
        return 'template';
    }
    
    public function initRepository()
    {
        $this->pushJoin(TemplateJoin::class);
    }
}
