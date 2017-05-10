<?php
/**
 * Created by PhpStorm.
 * User: dev03
 * Date: 10/05/17
 * Time: 12:06
 */

namespace ContinuousNet\LivnYouBundle\Repository\Criteria;

use ContinuousNet\LivnYouBundle\Repository\BaseRepository;

abstract class BaseCriteria
{
    abstract public function apply($queryBuilder, BaseRepository $repository);
}
