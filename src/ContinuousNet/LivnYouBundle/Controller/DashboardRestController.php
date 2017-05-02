<?php
/**
 * Created by PhpStorm.
 * User: dev03
 * Date: 02/05/17
 * Time: 11:21
 */

namespace ContinuousNet\LivnYouBundle\Controller;

use FOS\RestBundle\Controller\Annotations\QueryParam;
use FOS\RestBundle\Controller\Annotations\RouteResource;
use FOS\RestBundle\Controller\Annotations\View;
use FOS\RestBundle\Controller\Annotations\Get;
use FOS\RestBundle\Controller\Annotations\Post;
use FOS\RestBundle\Controller\Annotations\Put;
use FOS\RestBundle\Controller\Annotations\Patch;
use FOS\RestBundle\Controller\Annotations\Delete;
use FOS\RestBundle\Request\ParamFetcherInterface;
use FOS\RestBundle\View\View as FOSView;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;

/**
 * Class DashboardRestController
 * @package ContinuousNet\LivnYouBundle\Controller
 *
 * @RouteResource("Dashboard")
 */
class DashboardRestController extends BaseRESTController
{

    /**
     * Get Data needle for teh dashbord
     *
     * @View(serializerEnableMaxDepthChecks=true)
     *
     * @return Response
     */
    public function getDataAction()
    {
        $params = [];
        $data = [];
        $roles = $this->getUser()->getRoles();
        if (!empty($roles)) {
            foreach ($roles as $role) {
                if (substr_count($role, 'ADM') > 0) {
                    $params['filters']['creatorUser'] = $this->getUser()->getId();
                }
            }
        }
        $data['countUsers'] = $this->getDoctrine()->getRepository('LivnYouBundle:User')->count();
        $data['countGroups'] = $this->getDoctrine()->getRepository('LivnYouBundle:Group')->count();
        $data['countMeasurements'] = $this->getDoctrine()->getRepository('LivnYouBundle:Measurement')->count();
        return $data;
    }
}
