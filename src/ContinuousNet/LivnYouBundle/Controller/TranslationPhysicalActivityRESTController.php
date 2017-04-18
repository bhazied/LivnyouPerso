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
use FOS\RestBundle\Util\Codes;
use FOS\RestBundle\View\View as FOSView;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Finder\Finder;;
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
     * @Get("/{locale}/api/translationPhysicalActivities/{id}")
     *
     * @View(serializerEnableMaxDepthChecks=true)
     *
     * @return Response
     *
     */
    public function getAction($id)
    {
            $entity = $this->getDoctrine()->getRepository('LivnYouBundle:TranslationPhysicalActivity')->findOneById($id);
        $this->createSubDirectory($entity);
        return $entity;
    }

    /**
     * Get all Translation Physical Activity entities.
     *
     * @Get("/{locale}/api/translationPhysicalActivities")
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
            $this->createSubDirectory(new TranslationPhysicalActivity());
            $offset = $paramFetcher->get('offset');
            $limit = $paramFetcher->get('limit');
            $filter_operators = $paramFetcher->get('filter_operators') ? $paramFetcher->get('filter_operators') : array();
            $order_by = $paramFetcher->get('order_by') ? $paramFetcher->get('order_by') : array();
            $filters = !is_null($paramFetcher->get('filters')) ? $paramFetcher->get('filters') : array();
            $data = array(
                'inlineCount' => 0,
                'results' => array()
            );
            $em = $this->getDoctrine()->getManager();
            $qb = $em->createQueryBuilder();
            $qb->from('LivnYouBundle:TranslationPhysicalActivity', 'translationPhysicalActivity');
            $qb->leftJoin('ContinuousNet\LivnYouBundle\Entity\PhysicalActivity', 'physical_activity', \Doctrine\ORM\Query\Expr\Join::WITH, 'translationPhysicalActivity.physicalActivity = physical_activity.id');
            $qb->leftJoin('ContinuousNet\LivnYouBundle\Entity\User', 'creator_user', \Doctrine\ORM\Query\Expr\Join::WITH, 'translationPhysicalActivity.creatorUser = creator_user.id');
            $qb->leftJoin('ContinuousNet\LivnYouBundle\Entity\User', 'modifier_user', \Doctrine\ORM\Query\Expr\Join::WITH, 'translationPhysicalActivity.modifierUser = modifier_user.id');
            $textFields = array('translationPhysicalActivity.locale', 'translationPhysicalActivity.name', 'translationPhysicalActivity.athleticName');
            $memberOfConditions = array();
            foreach ($filters as $field => $value) {
                if (substr_count($field, '.') > 1) {
                    if ($value == 'true' || $value == 'or' || $value == 'and') {
                        list ($entityName, $listName, $listItem) = explode('.', $field);
                        if (!isset($memberOfConditions[$listName])) {
                            $memberOfConditions[$listName] = array('items' => array(), 'operator' => 'or');
                        }
                        if ($value == 'or' || $value == 'and') {
                            $memberOfConditions[$listName]['operator'] = $value;
                        } else {
                            $memberOfConditions[$listName]['items'][] = $listItem;
                        }
                    }
                    continue;
                }
                $key = str_replace('.', '', $field);
                if (!empty($value)) {
                   if (in_array($field, $textFields)) {
                       if (isset($filter_operators[$field]) && $filter_operators[$field] == 'eq') {
                           $qb->andWhere($qb->expr()->eq($field, $qb->expr()->literal($value)));
                       } else {
                           $qb->andWhere($qb->expr()->like($field, $qb->expr()->literal('%' . $value . '%')));
                       }
                   } else {
                       $qb->andWhere($field.' = :'.$key.'')->setParameter($key, $value);
                   }
                }
            }
            foreach ($memberOfConditions as $listName => $memberOfCondition) {
                if (!empty($memberOfCondition['items'])) {
                    if ($memberOfCondition['operator'] == 'or') {
                        $orX = $qb->expr()->orX();
                        foreach ($memberOfCondition['items'] as $i => $item) {
                            $orX->add($qb->expr()->isMemberOf(':'.$listName.'_value_'.$i, 'translationPhysicalActivity.'.$listName));
                            $qb->setParameter($listName.'_value_'.$i, $item);
                        }
                        $qb->andWhere($orX);
                    } else if ($memberOfCondition['operator'] == 'and') {
                        $andX = $qb->expr()->andX();
                        foreach ($memberOfCondition['items'] as $i => $item) {
                            $andX->add($qb->expr()->isMemberOf(':'.$listName.'_value_'.$i, 'translationPhysicalActivity.'.$listName));
                            $qb->setParameter($listName.'_value_'.$i, $item);
                        }
                        $qb->andWhere($andX);
                    }
                }
            }
            $qbList = clone $qb;
            $qb->select('count(translationPhysicalActivity.id)');
            $data['inlineCount'] = $qb->getQuery()->getSingleScalarResult();
            foreach ($order_by as $field => $direction) {
                $qbList->addOrderBy($field, $direction);
            }
            $qbList->select('translationPhysicalActivity');
            $qbList->setMaxResults($limit);
            $qbList->setFirstResult($offset);
            $qbList->groupBy('translationPhysicalActivity.id');
            $results = $qbList->getQuery()->getResult();
            if ($results) {
                $data['results'] = $results;
            }
            return $data;
        } catch (\Exception $e) {
            return FOSView::create($e->getMessage(), Codes::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Create a Translation Physical Activity entity.
     *
     * @Post("/{locale}/api/translationPhysicalActivities")
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
        $em = $this->getDoctrine()->getManager();
        $entity = new TranslationPhysicalActivity();
        $form = $this->createForm(new TranslationPhysicalActivityType(), $entity, array('method' => $request->getMethod()));
        $this->removeExtraFields($request, $form);
        $form->handleRequest($request);
        if ($form->isValid()) {
            $entity->setCreatorUser($this->getUser());
            $em->persist($entity);
            $em->flush();
            return $entity;
        }
    }

    /**
     * Update a Translation Physical Activity entity.
     *
     * @Put("/{locale}/api/translationPhysicalActivities/{id}")
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
            $entity = $this->getDoctrine()->getRepository('LivnYouBundle:TranslationPhysicalActivity')->findOneById($id);
            $em = $this->getDoctrine()->getManager();
            $request->setMethod('PATCH'); //Treat all PUTs as PATCH
            $form = $this->createForm(new TranslationPhysicalActivityType(), $entity, array('method' => $request->getMethod()));
            $this->removeExtraFields($request, $form);
            $form->handleRequest($request);
            if ($form->isValid()) {
                $entity->setModifierUser($this->getUser());
                $em->flush();
                return $entity;
            }
            return FOSView::create(array('errors' => $form->getErrors()), Codes::HTTP_INTERNAL_SERVER_ERROR);
        } catch (\Exception $e) {
            return FOSView::create($e->getMessage(), Codes::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Partial Update to a Translation Physical Activity entity.
     *
     * @Patch("/{locale}/api/translationPhysicalActivities/{id}")
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
     * Delete a Translation Physical Activity entity.
     *
     * @Delete("/{locale}/api/translationPhysicalActivities/{id}")
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
            $entity = $this->getDoctrine()->getRepository('LivnYouBundle:TranslationPhysicalActivity')->findOneById($id);
            $em = $this->getDoctrine()->getManager();
            $em->remove($entity);
            $em->flush();
            return null;
        } catch (\Exception $e) {
            return FOSView::create($e->getMessage(), Codes::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
    

}
