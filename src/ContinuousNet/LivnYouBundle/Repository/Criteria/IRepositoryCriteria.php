<?php
namespace ContinuousNet\LivnYouBundle\Repository\Criteria;

/**
 * Created by PhpStorm.
 * User: dev03
 * Date: 10/05/17
 * Time: 12:05
 */
interface IRepositoryCriteria
{
    public function getByCriteria(BaseCriteria $criteria);
}
