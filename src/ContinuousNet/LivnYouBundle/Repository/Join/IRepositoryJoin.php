<?php
/**
 * Created by PhpStorm.
 * User: dev03
 * Date: 10/05/17
 * Time: 17:57
 */

namespace ContinuousNet\LivnYouBundle\Repository\Join;

interface IRepositoryJoin
{
    public function pushJoin($join);

    public function getJoin();

    public function resetJoin();

    public function applyJoin();

    public function skipJoin($status = false);
}
