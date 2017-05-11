<?php

namespace ContinuousNet\LivnYouBundle\Controller;

use ContinuousNet\LivnYouBundle\Entity\TranslationPhysicalActivity;
use ContinuousNet\LivnYouBundle\Form\TranslationPhysicalActivityType;
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
use Symfony\Component\Finder\Finder;

;
use Symfony\Component\Finder\SplFileInfo;

/**
 * Translation Physical Activity REST Controller
 *
 * Manage TranslationPhysicalActivities
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
 * @see      TranslationPhysicalActivityRESTController
 * @since      Class available since Release 1.0
 * @access    public
 * @RouteResource("TranslationPhysicalActivity")
 */
class TranslationPhysicalActivityRESTController extends BaseRESTController
{
    /**
     * Get a Translation Physical Activity entity
     *
     * @View(serializerEnableMaxDepthChecks=true)
     *
     * @return Response
     *
     */
    public function getAction($idEntity)
    {
        try {
            $entity = $this->getDoctrine()->getRepository('LivnYouBundle:TranslationPhysicalActivity')->get(['id' => $idEntity]);
            $this->createSubDirectory($entity);
            return $entity;
        } catch (\Exception $e) {
            return FOSView::create($e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Get all Translation Physical Activity entities.
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
    public function cgetAction()
    {
        try {
            $data = array(
                'inlineCount' => $this->getDoctrine()
                    ->getRepository('LivnYouBundle:TranslationPhysicalActivity')
                    ->pushCriteria($this->get('livn_you.params_fetcher_criteria_counter'))
                    ->countAll(),
                'results' => $this->getDoctrine()
                    ->getRepository('LivnYouBundle:TranslationPhysicalActivity')
                    ->pushCriteria($this->get('livn_you.params_fetcher_criteria'))
                    ->getAll()
            );
            return $data;
        } catch (\Exception $e) {
            return FOSView::create($e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Create a Translation Physical Activity entity.
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
        $entity = new TranslationPhysicalActivity();
        $form = $this->createForm(TranslationPhysicalActivityType::class, $entity, array('method' => $request->getMethod()));
        $this->removeExtraFields($request, $form);
        $form->handleRequest($request);
        $form->submit($request->request->all());
        if ($form->isValid()) {
            return $this->getDoctrine()->getRepository('LivnYouBundle:TranslationPhysicalActivity')->store($entity, ['creatorUser' => $this->getUser()]);
        }
        return FOSView::create(
            ["status" => false, "message" => $this->getFormExactError($form->getErrors())],
            Response::HTTP_INTERNAL_SERVER_ERROR
        );
    }

    /**
     * Update a Translation Physical Activity entity.
     *
     * @View(serializerEnableMaxDepthChecks=true)
     *
     * @param Request $request
     * @param $idEntity
     *
     * @return Response
     */
    public function putAction(Request $request, $idEntity)
    {
        try {
            $entity = $this->getDoctrine()->getRepository('LivnYouBundle:TranslationPhysicalActivity')->get(['id' => $idEntity]);
            $request->setMethod('PATCH'); //Treat all PUTs as PATCH
            $form = $this->createForm(TranslationPhysicalActivityType::class, $entity, array('method' => $request->getMethod()));
            $this->removeExtraFields($request, $form);
            $form->handleRequest($request);
            $form->submit($request->request->all());
            if ($form->isValid()) {
                return $this->getDoctrine()->getRepository('LivnYouBundle:TranslationPhysicalActivity')->update($entity, ['modifierUser' => $this->getUser()]);
            }
            return FOSView::create(array('errors' => $form->getErrors()), Response::HTTP_INTERNAL_SERVER_ERROR);
        } catch (\Exception $e) {
            return FOSView::create($e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Partial Update to a Translation Physical Activity entity.
     *
     * @View(serializerEnableMaxDepthChecks=true)
     *
     * @param Request $request
     * @param $idEntity
     *
     * @return Response
     */
    public function patchAction(Request $request, $idEntity)
    {
        return $this->putAction($request, $idEntity);
    }

    /**
     * Delete a Translation Physical Activity entity.
     *
     * @View(statusCode=204)
     *
     * @param Request $request
     * @param $idEntity
     *
     * @return Response
     */
    public function deleteAction($idEntity)
    {
        try {
            $this->getDoctrine()->getRepository('LivnYouBundle:TranslationPhysicalActivity')->delete($idEntity);
            return null;
        } catch (\Exception $e) {
            return FOSView::create($e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
