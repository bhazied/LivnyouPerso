<?php

namespace ContinuousNet\LivnYouBundle\Controller;

use ContinuousNet\LivnYouBundle\Entity\Pathology;
use ContinuousNet\LivnYouBundle\Form\PathologyType;
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
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Finder\Finder;;
use Symfony\Component\Finder\SplFileInfo;

/**
 * Pathology REST Controller
 * 
 * Manage Pathologies 
 * 
 * PHP version 5.4.4
 * 
 * @category   Symfony 2 REST Controller
 * @package  ContinuousNet\LivnYouBundle\Controller
 * @author    Sahbi KHALFALLAH <sahbi.khalfallah@continuousnet.com>
 * @copyright  2017 CONTINUOUS NET
 * @license  CONTINUOUS NET REGULAR LICENSE
 * @version  Release: 1.0
 * @link    http://livnyou.continuousnet.com/ContinuousNet/LivnYouBundle/Controller
 * @see      PathologyRESTController
 * @since      Class available since Release 1.0
 * @access    public
 * @RouteResource("Pathology")
 */
class PathologyRESTController extends BaseRESTController
{
    /**
     * Get a Pathology entity
     *
     * @View(serializerEnableMaxDepthChecks=true)
     *
     * @return Response
     *
     */
    public function getAction($id)
    {
        try {
            $entity = $this->getDoctrine()->getRepository('LivnYouBundle:Pathology')->get(['id' => $id]);
            $entity = $this->translateEntity($entity);
            $this->createSubDirectory($entity);
            return $entity;
        } catch (\Exception $e) {
            return FOSView::create($e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Get all Pathology entities.
     *
     * @View(serializerEnableMaxDepthChecks=true)
     *
     * @param ParamFetcherInterface $paramFetcher
     *
     * @return Response
     *
     * @QueryParam(name="offset", requirements="\d+", nullable=true, description="Offset from which to start listing notes.")
     * @QueryParam(name="limit", requirements="\d+", default="1000", description="How many notes to return.")
     * @QueryParam(name="filter_operators", nullable=true, map=true, description="Filter fields operators.")
     * @QueryParam(name="order_by", nullable=true, map=true, description="Order by fields. Must be an array ie. &order_by[name]=ASC&order_by[description]=DESC")
     * @QueryParam(name="filters", nullable=true, map=true, description="Filter by fields. Must be an array ie. &filters[id]=3")
     */
    public function cgetAction(ParamFetcherInterface $paramFetcher)
    {
        try {
            $this->createSubDirectory(new Pathology());
            $offset = $paramFetcher->get('offset');
            $limit = $paramFetcher->get('limit');
            $filter_operators = $paramFetcher->get('filter_operators') ? $paramFetcher->get('filter_operators') : array();
            $order_by = $paramFetcher->get('order_by') ? $paramFetcher->get('order_by') : array();
            $filters = !is_null($paramFetcher->get('filters')) ? $paramFetcher->get('filters') : array();
            $params = compact('offset','limit','filter_operators','order_by','filters');
            $data = array(
                'inlineCount' => 0,
                'results' => array()
            );
            list($inlineCount, $results) = array_values($this->getDoctrine()->getRepository('LivnYouBundle:Pathology')->getAll($params));
            $data = array(
                'inlineCount' => $inlineCount,
                'results' => $this->translateEntities($results)
            );
            return $data;

        } catch (\Exception $e) {
            return FOSView::create($e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Create a Pathology entity.
     *
     * @View(statusCode=201, serializerEnableMaxDepthChecks=true)
     *
     * @param Request $request
     *
     * @return Response
     *
     */
    public function postAction(Request $request)
    {
        $entity = new Pathology();
        $form = $this->createForm(PathologyType::class, $entity, array('method' => $request->getMethod()));
        $this->removeExtraFields($request, $form);
        $form->handleRequest($request);
        $form->submit($request->request->all());
        if ($form->isValid()) {
            return $this->getDoctrine()->getRepository('LivnYouBundle:Pathology')->store($entity, ['creatorUser' => $this->getUser()]);
        }
        return FOSView::create(
            ["status" => false, "message" => $this->getFormExactError($form->getErrors())],
            Response::HTTP_INTERNAL_SERVER_ERROR
        );
    }

    /**
     * Update a Pathology entity.
     *
     * @View(serializerEnableMaxDepthChecks=true)
     *
     * @param Request $request
     * @param $id
     *
     * @return Response
     */
    public function putAction(Request $request, $id)
    {
        try {
            $entity = $this->getDoctrine()->getRepository('LivnYouBundle:Pathology')->get(['id' => $id]);
            $request->setMethod('PATCH'); //Treat all PUTs as PATCH
            $roles = $this->getUser()->getRoles();
            if (!empty($roles)) {
                foreach ($roles as $role) {
                   if (substr_count($role, 'MAN') > 0) {
                       if ($entity->getCreatorUser()->getId() != $this->getUser()->getId()) {
                           return FOSView::create('Not authorized', Response::HTTP_FORBIDDEN);
                       }
                   }
                }
            }
            $form = $this->createForm(PathologyType::class, $entity, array('method' => $request->getMethod()));
            $this->removeExtraFields($request, $form);
            $form->handleRequest($request);
            $form->submit($request->request->all());
            if ($form->isValid()) {
                return $this->getDoctrine()->getRepository('LivnYouBundle:Pathology')->update($entity, ['modifierUser' => $this->getUser()]);
            }
            return FOSView::create(array('errors' => $form->getErrors()), Response::HTTP_INTERNAL_SERVER_ERROR);
        } catch (\Exception $e) {
            return FOSView::create($e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Partial Update to a Pathology entity.
     *
     * @View(serializerEnableMaxDepthChecks=true)
     *
     * @param Request $request
     * @param $id
     *
     * @return Response
     */
    public function patchAction(Request $request, $id)
    {
        return $this->putAction($request, $id);
    }

    /**
     * Delete a Pathology entity.
     *
     * @View(statusCode=204)
     *
     * @param Request $request
     * @param $id
     *
     * @return Response
     */
    public function deleteAction(Request $request, $id)
    {
        try {
            $entity = $this->getDoctrine()->getRepository('LivnYouBundle:Pathology')->get(['id' => $id]);
            $roles = $this->getUser()->getRoles();
            if (!empty($roles)) {
                foreach ($roles as $role) {
                   if (substr_count($role, 'MAN') > 0) {
                       if ($entity->getCreatorUser()->getId() != $this->getUser()->getId()) {
                           return FOSView::create('Not authorized', Response::HTTP_FORBIDDEN);
                       }
                   }
                }
            }
            $this->getDoctrine()->getRepository('LivnYouBundle:Pathology')->delete($id);
            return null;
        } catch (\Exception $e) {
            return FOSView::create($e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
    

}
