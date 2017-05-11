<?php
namespace ContinuousNet\LivnYouBundle\Repository\Join;

use ContinuousNet\LivnYouBundle\Repository\BaseRepository;

/**
 * Created by PhpStorm.
 * User: dev03
 * Date: 10/05/17
 * Time: 17:28
 */
abstract class BaseJoin
{
    abstract public function apply($queryBuilder);
}
