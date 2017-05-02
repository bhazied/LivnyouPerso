<?php
/**
 * Created by PhpStorm.
 * User: dev03
 * Date: 02/05/17
 * Time: 15:57
 */

namespace ContinuousNet\LivnYouBundle\Controller;


use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class ThumbController extends Controller
{

    public function indexAction(Request $request)
    {
        $image = $request->get('image');
        return $this->get('liip_imagine.controller')->filterAction($request, $image, 'thumb');
    }
}