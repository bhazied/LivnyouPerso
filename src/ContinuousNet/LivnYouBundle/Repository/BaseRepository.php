<?php
/**
 * Created by PhpStorm.
 * User: dev03
 * Date: 02/05/17
 * Time: 11:29
 */

namespace ContinuousNet\LivnYouBundle\Repository;

use Doctrine\ORM\EntityRepository;

abstract class BaseRepository extends EntityRepository
{

    /* whene i get the time i will redefine and impliment contract*/

    abstract public function model();

    public function makeModel()
    {
        $model = $this->createQueryBuilder($this->model());
        return $model;
    }
}
