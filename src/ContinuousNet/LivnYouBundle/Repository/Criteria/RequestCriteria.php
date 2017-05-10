<?php
/**
 * Created by PhpStorm.
 * User: dev03
 * Date: 10/05/17
 * Time: 12:43
 */

namespace ContinuousNet\LivnYouBundle\Repository\Criteria;

use ContinuousNet\LivnYouBundle\Repository\BaseRepository;
use Symfony\Component\HttpFoundation\Request;

class RequestCriteria extends BaseCriteria
{
    protected $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    public function apply($queryBuilder, BaseRepository $repository)
    {
        // TODO: Implement apply() method.
    }
}
